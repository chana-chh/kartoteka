<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Zaduzenje;
use App\Models\Racun;
use App\Models\Reprogram;
use App\Models\Uplata;

class TransakcijeController extends Controller
{

    public function getKartonPregled($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);
        $broj_uplate = count($karton->uplate());
        $this->render($response, 'transakcije_pregled.twig', compact('karton', 'broj_uplate'));
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

    public function getZaduzivanjeTakse($request, $response)
    {
        $model_cene = new Cena();
        $takse = $model_cene->all();

        $this->render($response, 'zaduzivanje_taksi.twig', compact('takse'));
    }

    public function postZaduzivanjeTakse($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $model_cene = new Cena();
        $cena = $model_cene->find($data['taksa_id']);

        $iznos =  (float) $cena->taksa;
        $godina = $cena->godina();

        $validation_rules = [
            'taksa_id' => [
                'required' => true,
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        $model_karton = new Karton();
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE godina = :god AND tip = 1;";
        $broj = $model_karton->fetch($sql, [':god' => $godina])[0]->broj;
        if ($broj > 0) {
            $this->flash->addMessage('danger', 'Vec postoji zaduženje za odabranu godinu');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
        }
        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
        } else {
            $podaci = [
                ':karton_id' => 0,
                ':tip' => 'taksa',
                ':iznos' => 0,
                ':godina' => (int) $godina,
                ':razduzeno' => 0,
                ':datum_zaduzenja' => date('Y-m-d'),
                ':korisnik_id_zaduzio' => $this->auth->user()->id,
            ];

            $kartoni = $model_karton->sviAktivni();
            $pdo = $model_karton->getDb()->getPDO();
            $sql = "INSERT INTO `zaduzenja` (karton_id, tip, iznos, godina, razduzeno, datum_zaduzenja, korisnik_id_zaduzio)
                VALUES (:karton_id, :tip, :iznos, :godina, :razduzeno, :datum_zaduzenja, :korisnik_id_zaduzio);";
            $stmt = $pdo->prepare($sql);

            $pdo->beginTransaction();
            foreach ($kartoni as $karton) {
                $podaci[':karton_id'] = $karton->id;
                $podaci[':iznos'] = $iznos * $karton->broj_mesta;
                $stmt->execute($podaci);
            }
            $pdo->commit();
            $this->flash->addMessage('success', 'Svi aktivni kartoni su uspešno zaduženi.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
        }
    }

    public function getZaduzivanjeZakup($request, $response)
    {
        $model_cene = new Cena();
        $model_kartoni = new Karton();
        $cene = $model_cene->all();

        $tekuca_godina = (int) date('Y');
        $sql = "SELECT * FROM kartoni
                WHERE id NOT IN (
                    SELECT karton_id FROM  zaduzenja
                    WHERE  tip = 2 AND godina = {$tekuca_godina}
                ) AND aktivan = 1 ORDER BY kartoni.id;";
        $nezaduzeni_kartoni = $model_kartoni->fetch($sql);

        $this->render($response, 'zaduzivanje_zakupa.twig', compact('cene', 'nezaduzeni_kartoni'));
    }

    public function postZaduzivanjeZakup($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $model_cene = new Cena();
        $cena = $model_cene->find($data['zakup_id']);

        $iznos = (float) $cena->zakup / 10;
        $godina = $cena->godina();

        $validation_rules = [
            'zakup_id' => [
                'required' => true,
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.zakup'));
        } else {
            $model_karton = new Karton();
            $pocetna_godina = (int) $godina;
            $sql = "SELECT * FROM kartoni
                WHERE id NOT IN (
                    SELECT karton_id FROM  zaduzenja
                    WHERE  tip = 2 AND godina = {$pocetna_godina}
                ) AND aktivan = 1 ORDER BY kartoni.id;";
            $kartoni = $model_karton->fetch($sql);
            $podaci = [
                ':karton_id' => 0,
                ':tip' => 'zakup',
                ':iznos' => 0,
                ':godina' => (int) $pocetna_godina,
                ':razduzeno' => 0,
                ':datum_zaduzenja' => date('Y-m-d'),
                ':korisnik_id_zaduzio' => $this->auth->user()->id,
            ];

            $pdo = $model_karton->getDb()->getPDO();
            $sql = "INSERT INTO `zaduzenja` (karton_id, tip, iznos, godina, razduzeno, datum_zaduzenja, korisnik_id_zaduzio)
                VALUES (:karton_id, :tip, :iznos, :godina, :razduzeno, :datum_zaduzenja, :korisnik_id_zaduzio);";
            $stmt = $pdo->prepare($sql);
            $pdo->beginTransaction();

            for ($i = 0; $i < 10; $i++) {
                $podaci[':godina'] = $pocetna_godina + $i;
                foreach ($kartoni as $karton) {
                    $podaci[':karton_id'] = $karton->id;
                    $podaci[':iznos'] = $iznos * $karton->broj_mesta;
                    $stmt->execute($podaci);
                }
            }

            $pdo->commit();
            $this->flash->addMessage('success', 'Svi aktivni kartoni su uspešno zaduženi.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.zakup'));
        }
    }

    public function postUplata($request, $response)
    {
        $data = $request->getParams();
        $karton_id = $data['karton_id'];
        $korisnik_id = $this->auth->user()->id;
        // podaci za uplatu
        $uplata_data = [
            'karton_id' => $karton_id,
            'iznos' => (float) $data['uplata_iznos'],
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
                'min' => 0.01,
            ],
            'uplata_datum' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja uplate i razduživanja.');
            return $response->withRedirect($this->router->pathFor('transakcije.razduzivanje', ['id' => $karton_id]));
        } else {
            $model_uplata = new Uplata();
            $model_uplata->insert($uplata_data);
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
