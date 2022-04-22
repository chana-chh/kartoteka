<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Staraoc;
use App\Models\Zaduzenje;
use App\Models\Racun;
use App\Models\Reprogram;
use App\Models\Uplata;

class TransakcijeController extends Controller
{

    public function getKartonPregled($request, $response, $args)
    {
        $staraoc_id = $args['id'];
        $zaduzenost = isset($args['z']) ? (int) $args['z'] : 0;
        if($zaduzenost < 0 | $zaduzenost > 2)
        {
            $zaduzenost = 0;
        }
        $staraoc = (new Staraoc())->find($staraoc_id);

        // dd($staraoc->dugZaRacune());

        // broj uplata prepraviti po staraocu, a ne kartonu
        $broj_uplata = count($staraoc->karton()->uplate());
        $model_cene = new Cena();
        $cena_takse = $model_cene->taksa();
        $cena_zakupa = $model_cene->zakup();

        $this->render($response, 'transakcije_pregled.twig', compact('staraoc', 'broj_uplata', 'cena_takse', 'cena_zakupa', 'zaduzenost'));
    }

    public function getKartonPregledStampa($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);
        $model_cena = new Cena();
        $taksa_vazeca = $model_cena->taksa();
        $zakup_vazeci = $model_cena->zakup() / 10;
        $this->render($response, 'print/transakcije_pregled.twig', compact('karton', 'taksa_vazeca', 'zakup_vazeci'));
    }

    public function getKartonRazduzivanje($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);

        $model_cene = new Cena();
        $cena_takse = $model_cene->taksa();
        $cena_zakupa = $model_cene->zakup() / 10;

        $this->render($response, 'transakcije_razduzivanje.twig', compact('karton', 'cena_takse', 'cena_zakupa'));
    }

    public function getZaduzivanje($request, $response)
    {
        $model = new Cena();
        $taksa = $model->taksa();
        $zakup = $model->zakup();

        $zaduzena_godina = false;
        $upozorenje = '';

        $curr_year = (int) date('Y');

        $model_karton = new Karton();
        $model_zaduzenje = new Zaduzenje();

        $sql_broj_kartona = "SELECT COUNT(*) AS broj_kartona FROM kartoni WHERE aktivan = 1;";
        $sql_broj_zaduzenja = "SELECT COUNT(*) AS broj_zaduzenja FROM zaduzenja WHERE godina = {$curr_year};";

        $broj_kartona = $model_karton->fetch($sql_broj_kartona)[0]->broj_kartona;
        $broj_zaduzenja = $model_zaduzenje->fetch($sql_broj_zaduzenja)[0]->broj_zaduzenja;

        // vise 75% kartona je zaduzeno za godinu
        if ($broj_zaduzenja > ($broj_kartona * 1.5))
        {
            $upozorenje = "Većina kartona je zadužena za tekuću godinu ({$curr_year})";
            $zaduzena_godina = true;
        }
        else
        {
            $upozorenje = "Većina kartona nije zadužena za tekuću godinu ({$curr_year})";
            $zaduzena_godina = false;
        }

        $this->render($response, 'zaduzivanje.twig', compact('taksa', 'zakup', 'upozorenje', 'zaduzena_godina'));
    }

    public function postZaduzivanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $cur_year = (int) date('Y');

        $validation_rules = [
            'taksa' => [
                'required' => true,
            ],
            'zakup' => [
                'required' => true,
            ],
            'godina' => [
                'required' => true,
                'min' => $cur_year - 1,
                'max' => $cur_year + 1,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors())
        {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje'));
        }
        else
        {
            $model_karton = new Karton();
            $model_zaduzenja = new Zaduzenje();

            $taksa =  (float) $data['taksa'];
            $zakup =  (float) $data['zakup'];
            $godina = (int) $data['godina'];

            $podaci = [
                ':karton_id' => 0,
                ':staraoc_id' => 0,
                ':tip' => 'taksa',
                ':godina' => $godina,
                ':iznos_zaduzeno' => 0,
                ':iznos_razduzeno' => 0,
                ':razduzeno' => 0,
                ':datum_zaduzenja' => date('Y-m-d'),
                ':korisnik_id_zaduzio' => $this->auth->user()->id,
            ];
            
            $kartoni = $model_karton->sviAktivni();
            $pdo = $model_karton->getDb()->getPDO();
            $sql = "INSERT INTO `zaduzenja` (karton_id, staraoc_id, tip, iznos_zaduzeno, iznos_razduzeno, godina, razduzeno, datum_zaduzenja, korisnik_id_zaduzio)
                    VALUES (:karton_id, :staraoc_id, :tip, :iznos_zaduzeno, :iznos_razduzeno, :godina, :razduzeno, :datum_zaduzenja, :korisnik_id_zaduzio);";
            $stmt = $pdo->prepare($sql);

            $pdo->beginTransaction();

            foreach ($kartoni as $karton)
            {
                $staraoci = $karton->aktivniStaraoci();
                $br_mesta = $karton->broj_mesta;
                $br_staraoca = count($staraoci);
                
                foreach ($staraoci as $staraoc)
                {
                    // proveriti da li je zaduzen taksom
                    $tip = 1;
                    $sql_taksa = "SELECT COUNT(*) AS br_taksi FROM zaduzenja WHERE karton_id = {$karton->id}
                                    AND staraoc_id = {$staraoc->id} AND tip = {$tip} AND godina = {$godina}";
                    $br_taksi = $model_zaduzenja->fetch($sql_taksa)[0]->br_taksi;

                    if ($br_taksi === 0)
                    {
                        // odrediti taksu
                        $iznos_takse = (float) ($taksa * $br_mesta / $br_staraoca);
                        
                        $podaci[':karton_id'] = $karton->id;
                        $podaci[':staraoc_id'] = $staraoc->id;
                        $podaci[':tip'] = 'taksa';
                        $podaci[':iznos_zaduzeno'] = $iznos_takse;

                        $stmt->execute($podaci);
                    }

                    // proveriti da li je zaduzen zakupom
                    $tip = 2;
                    $sql_zakup = "SELECT COUNT(*) AS br_zakupa FROM zaduzenja WHERE karton_id = {$karton->id}
                                    AND staraoc_id = {$staraoc->id} AND tip = {$tip} AND godina = {$godina}";
                    $br_zakupa = $model_zaduzenja->fetch($sql_zakup)[0]->br_zakupa;

                    if ($br_zakupa === 0)
                    {
                        // odrediti zakup
                        $iznos_zakupa = (float) ($zakup * $br_mesta / $br_staraoca);

                        $podaci[':karton_id'] = $karton->id;
                        $podaci[':staraoc_id'] = $staraoc->id;
                        $podaci[':tip'] = 'zakup';
                        $podaci[':iznos_zaduzeno'] = $iznos_zakupa;

                        $stmt->execute($podaci);
                    }
                }                
            }

            $pdo->commit();

            $this->flash->addMessage('success', 'Svi aktivni kartoni su uspešno zaduženi.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje'));
        }
    }
    
    // public function getZaduzivanjeTakse($request, $response)
    // {
    //     $model = new Cena();
    //     $taksa = $model->taksa();
    //     $zakup = $model->zakup();

    //     $this->render($response, 'zaduzivanje_taksi.twig', compact('takse'));
    // }

    // public function postZaduzivanjeTakse($request, $response)
    // {
    //     $data = $request->getParams();
    //     unset($data['csrf_name']);
    //     unset($data['csrf_value']);

    //     $model_cene = new Cena();
    //     $cena = $model_cene->find($data['taksa_id']);

    //     $iznos =  (float) $cena->taksa;
    //     $godina = $cena->godina();

    //     $validation_rules = [
    //         'taksa_id' => [
    //             'required' => true,
    //         ]
    //     ];

    //     $this->validator->validate($data, $validation_rules);

    //     $model_karton = new Karton();
    //     $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE godina = :god AND tip = 1;";
    //     $broj = $model_karton->fetch($sql, [':god' => $godina])[0]->broj;
    //     if ($broj > 0) {
    //         $this->flash->addMessage('danger', 'Vec postoji zaduženje za odabranu godinu');
    //         return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
    //     }
    //     if ($this->validator->hasErrors()) {
    //         $this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
    //         return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
    //     } else {
    //         $podaci = [
    //             ':karton_id' => 0,
    //             ':tip' => 'taksa',
    //             ':iznos' => 0,
    //             ':godina' => (int) $godina,
    //             ':razduzeno' => 0,
    //             ':datum_zaduzenja' => date('Y-m-d'),
    //             ':korisnik_id_zaduzio' => $this->auth->user()->id,
    //         ];

    //         $kartoni = $model_karton->sviAktivni();
    //         $pdo = $model_karton->getDb()->getPDO();
    //         $sql = "INSERT INTO `zaduzenja` (karton_id, tip, iznos, godina, razduzeno, datum_zaduzenja, korisnik_id_zaduzio)
    //             VALUES (:karton_id, :tip, :iznos, :godina, :razduzeno, :datum_zaduzenja, :korisnik_id_zaduzio);";
    //         $stmt = $pdo->prepare($sql);

    //         $pdo->beginTransaction();
    //         foreach ($kartoni as $karton) {
    //             $podaci[':karton_id'] = $karton->id;
    //             $podaci[':iznos'] = $iznos * $karton->broj_mesta;
    //             $stmt->execute($podaci);
    //         }
    //         $pdo->commit();
    //         $this->flash->addMessage('success', 'Svi aktivni kartoni su uspešno zaduženi.');
    //         return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
    //     }
    // }

    // public function getZaduzivanjeZakup($request, $response)
    // {
    //     $model_cene = new Cena();
    //     $model_kartoni = new Karton();
    //     $cene = $model_cene->all();

    //     $tekuca_godina = (int) date('Y');
    //     $sql = "SELECT * FROM kartoni
    //             WHERE id NOT IN (
    //                 SELECT karton_id FROM  zaduzenja
    //                 WHERE  tip = 2 AND godina = {$tekuca_godina}
    //             ) AND aktivan = 1 ORDER BY kartoni.id;";
    //     $nezaduzeni_kartoni = $model_kartoni->fetch($sql);

    //     $this->render($response, 'zaduzivanje_zakupa.twig', compact('cene', 'nezaduzeni_kartoni'));
    // }

    // public function postZaduzivanjeZakup($request, $response)
    // {
    //     $data = $request->getParams();
    //     unset($data['csrf_name']);
    //     unset($data['csrf_value']);

    //     $model_cene = new Cena();
    //     $cena = $model_cene->find($data['zakup_id']);

    //     $iznos = (float) $cena->zakup / 10;
    //     $godina = $cena->godina();

    //     $validation_rules = [
    //         'zakup_id' => [
    //             'required' => true,
    //         ]
    //     ];

    //     $this->validator->validate($data, $validation_rules);

    //     if ($this->validator->hasErrors()) {
    //         $this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
    //         return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.zakup'));
    //     } else {
    //         $model_karton = new Karton();
    //         $pocetna_godina = (int) $godina;
    //         $sql = "SELECT * FROM kartoni
    //             WHERE id NOT IN (
    //                 SELECT karton_id FROM  zaduzenja
    //                 WHERE  tip = 2 AND godina = {$pocetna_godina}
    //             ) AND aktivan = 1 ORDER BY kartoni.id;";
    //         $kartoni = $model_karton->fetch($sql);
    //         $podaci = [
    //             ':karton_id' => 0,
    //             ':tip' => 'zakup',
    //             ':iznos' => 0,
    //             ':godina' => (int) $pocetna_godina,
    //             ':razduzeno' => 0,
    //             ':datum_zaduzenja' => date('Y-m-d'),
    //             ':korisnik_id_zaduzio' => $this->auth->user()->id,
    //         ];

    //         $pdo = $model_karton->getDb()->getPDO();
    //         $sql = "INSERT INTO `zaduzenja` (karton_id, tip, iznos, godina, razduzeno, datum_zaduzenja, korisnik_id_zaduzio)
    //             VALUES (:karton_id, :tip, :iznos, :godina, :razduzeno, :datum_zaduzenja, :korisnik_id_zaduzio);";
    //         $stmt = $pdo->prepare($sql);
    //         $pdo->beginTransaction();

    //         for ($i = 0; $i < 10; $i++) {
    //             $podaci[':godina'] = $pocetna_godina + $i;
    //             foreach ($kartoni as $karton) {
    //                 $podaci[':karton_id'] = $karton->id;
    //                 $podaci[':iznos'] = $iznos * $karton->broj_mesta;
    //                 $stmt->execute($podaci);
    //             }
    //         }

    //         $pdo->commit();
    //         $this->flash->addMessage('success', 'Svi aktivni kartoni su uspešno zaduženi.');
    //         return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.zakup'));
    //     }
    // }

    public function postUplata($request, $response)
    {
        $data = $request->getParams();
        $karton_id = $data['karton_id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);
        $korisnik_id = $this->auth->user()->id;
        $iznos = (float) $data['uplata_iznos'];
        // podaci za uplatu
        $uplata_data = [
            'karton_id' => $karton_id,
            'iznos' => $iznos,
            'datum' => $data['uplata_datum'],
            'priznanica' => $data['uplata_priznanica'],
            'napomena' => $data['uplata_napomena'],
            'korisnik_id' => $korisnik_id,
        ];
        // niz id-a zaduzenja
        $zaduzenja_data = isset($data['razduzeno-zaduzenje']) ? $data['razduzeno-zaduzenje'] : [];
        // niz id-a racuna
        $racuni_data = isset($data['razduzeno-racuni']) ? $data['razduzeno-racuni'] : [];
        // niz id-a reprograma
        $reprogrami_data = isset($data['razduzeno-reprogrami']) ? $data['razduzeno-reprogrami'] : [];

        $validation_rules = [
            'karton_id' => [
                'required' => true,
            ],
            'uplata_iznos' => [
                'required' => true,
                'min' => 0,
            ],
            'uplata_datum' => [
                'required' => true,
            ],
        ];

        // provera da li je iznos >= sva zaduzenja za razduzivanje
        $model_zaduzenje = new Zaduzenje();
        $model_racun = new Racun();
        $model_reprogram = new Reprogram();
        $model_cena = new Cena();

        $taksa = $model_cena->taksa();
        $zakup = $model_cena->zakup() / 10;

        $iznos_za_razduzenje = 0;

        if (!empty($zaduzenja_data)) {
            $zad = implode(", ", $zaduzenja_data);
            $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE id IN ($zad) AND tip = 'taksa';";
            $br = (int) $model_zaduzenje->fetch($sql)[0]->broj;
            $iznos_za_razduzenje += $br * $taksa * $karton->broj_mesta;
            $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE id IN ($zad) AND tip = 'zakup';";
            $br = (int) $model_zaduzenje->fetch($sql)[0]->broj;
            $iznos_za_razduzenje += $br * $zakup * $karton->broj_mesta;
        }
        if (!empty($racuni_data)) {
            $rac = implode(", ", $racuni_data);
            $sql = "SELECT SUM(iznos) AS zbir FROM racuni WHERE id IN ($rac);";
            $zbir = $model_racun->fetch($sql)[0]->zbir;
            $iznos_za_razduzenje += $zbir;
        }
        if (!empty($reprogrami_data)) {
            $rep = implode(", ", $reprogrami_data);
            $sql = "SELECT * FROM reprogrami WHERE id IN ($rep);";
            $reprogrami = $model_reprogram->fetch($sql);
            $zbir = 0;
            foreach ($reprogrami as $rep) {
                $zbir += $rep->dug();
            }
            $iznos_za_razduzenje += $zbir;
        }

        $razlika = $iznos + $karton->saldo - $iznos_za_razduzenje;

        if ($razlika < 0) {
            $this->flash->addMessage('danger', 'Iznos uplate nije daovoljan za razduživanje.');
            return $response->withRedirect($this->router->pathFor('transakcije.razduzivanje', ['id' => $karton_id]));
        }

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja uplate i razduživanja.');
            return $response->withRedirect($this->router->pathFor('transakcije.razduzivanje', ['id' => $karton_id]));
        } else {
            $model_uplata = new Uplata();
            $model_uplata->insert($uplata_data);
            $sql = "UPDATE kartoni SET saldo = {$razlika} WHERE id={$karton_id};";
            $model_karton->run($sql);
            if (!empty($zaduzenja_data)) {
                $zad = implode(", ", $zaduzenja_data);
                $sql_zaduzenja = "UPDATE zaduzenja
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}
                    WHERE id IN ($zad);";
                $model_uplata->run($sql_zaduzenja);
            }
            if (!empty($racuni_data)) {
                $rac = implode(", ", $racuni_data);
                $sql_racuni = "UPDATE racuni
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}
                    WHERE id IN ($rac);";
                $model_uplata->run($sql_racuni);
            }
            if (!empty($reprogrami_data)) {
                $rep = implode(", ", $reprogrami_data);
                $sql_zaduzenja = "UPDATE zaduzenja
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}
                    WHERE reprogram_id IN ($rep);";
                $model_uplata->run($sql_zaduzenja);
                $sql_racuni = "UPDATE racuni
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}
                    WHERE reprogram_id IN ($rep);";
                $model_uplata->run($sql_racuni);
                $sql_reprogram = "UPDATE reprogrami
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}, preostalo_rata = 0
                    WHERE id IN ($rep);";
                $model_uplata->run($sql_reprogram);
            }
            $this->flash->addMessage('success', 'Uplata je uspešno sačuvana, a odabrane stavke su razdužene.');
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
        }
    }

    public function getOpomene($request, $response)
    {
        $model_karton = new Karton();
        $kartoni = $model_karton->sviAktivni();

        $this->render($response, 'print/opomene.twig', compact('kartoni'));
    }


    public function postZaduzenjeBrisanje($request, $response)
    {
        $id = (int) $request->getParam('modal_zaduzenje_id');
        $karton_id = (int) $request->getParam('karton_id');
        $modelZaduzenja = new Zaduzenje();
        $success = $modelZaduzenja->deleteOne($id);
        if ($success) {
            $this->flash->addMessage('success', "Zaduženje je uspešno obrisano.");
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja zaduženja.");
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
        }
    }

    public function postSveBrisanje($request, $response)
    {
        $karton_id = (int) $request->getParam('karton_id');

        $sqlz = "DELETE FROM zaduzenja WHERE karton_id = :kar;";
        $modelZaduzenja = new Zaduzenje();
        $successz = $modelZaduzenja->run($sqlz, [':kar' => $karton_id]);

        $sqlr = "DELETE FROM racuni WHERE karton_id = :kar;";
        $modelRacuna = new Racun();
        $successr = $modelRacuna->run($sqlr, [':kar' => $karton_id]);

        $sqle = "DELETE FROM reprogrami WHERE karton_id = :kar;";
        $modelReprogram = new Reprogram();
        $successe = $modelReprogram->run($sqle, [':kar' => $karton_id]);

        $sqlu = "DELETE FROM uplate WHERE karton_id = :kar;";
        $modelUplata = new Uplata();
        $successu = $modelUplata->run($sqlu, [':kar' => $karton_id]);

        if ($successz || $successr || $successe || $successu) {
            $this->flash->addMessage('success', "Zaduženje, računi i uplate su uspešno obrisane.");
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja zaduženja.");
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
        }
    }
}
