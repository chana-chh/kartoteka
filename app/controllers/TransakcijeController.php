<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Zaduzenje;
use App\Models\Racun;

class TransakcijeController extends Controller
{
    public function getCene($request, $response)
    {
        $model = new Cena();
        $cene = $model->all('datum', 'DESC');
        $taksa = $model->taksa();
        $zakup = $model->zakup();

        $this->render($response, 'cene.twig', compact('cene', 'taksa', 'zakup'));
    }

    public function getKarton($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);
        $model_zaduzenje = new Zaduzenje();
        $takse = $model_zaduzenje->nerazduzeneTakseZaKarton($karton_id);
        $broj_taksi = count($takse);
        $zakupi = $model_zaduzenje->nerazduzeniZakupiZaKarton($karton_id);
        $broj_zakupa = count($zakupi);
        $model_cena = new Cena();
        $cena_takse = (float) $model_cena->taksa();
        $cena_zakupa = (float) $model_cena->zakup() / 10;
        $dug = ($broj_taksi * $cena_takse) + ($broj_zakupa * $cena_zakupa);
        $model_racun = new Racun();
        $racuni = $model_racun->nerazduzeniRacuniZaKarton($karton_id);
        $broj_racuna = count($racuni);
        $dug += (float) $model_racun->dugZaKarton($karton_id);

        $this->render(
            $response,
            'transakcije.twig',
            compact(
                'karton',
                'takse',
                'broj_taksi',
                'zakupi',
                'broj_zakupa',
                'dug',
                'cena_takse',
                'cena_zakupa',
                'racuni',
                'broj_racuna'
            )
        );
    }

    public function getKartonPregled($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);

        $model_zaduzenje = new Zaduzenje();
        $takse = $model_zaduzenje->takseZaKarton($karton_id);
        $takse_nerazduzene = $model_zaduzenje->nerazduzeneTakseZaKarton($karton_id);
        $broj_taksi_nearazduzenih = count($takse_nerazduzene);
        $zakupi = $model_zaduzenje->zakupiZaKarton($karton_id);
        $zakupi_nearzduzeni = $model_zaduzenje->nerazduzeniZakupiZaKarton($karton_id);
        $broj_zakupa_nerazduzenih = count($zakupi_nearzduzeni);

        $model_cena = new Cena();
        $cena_takse = (float) $model_cena->taksa();
        $cena_zakupa = (float) $model_cena->zakup() / 10;

        $dug = ($broj_taksi_nearazduzenih * $cena_takse) + ($broj_zakupa_nerazduzenih * $cena_zakupa);

        $model_racun = new Racun();
        $racuni = $model_racun->racuniZaKarton($karton_id);
        $racuni_nerazduzeni = $model_racun->nerazduzeniRacuniZaKarton($karton_id);
        $broj_racuna_nerazduzenih = count($racuni_nerazduzeni);

        $dug += (float) $model_racun->dugZaKarton($karton_id);

        $this->render(
            $response,
            'transakcije_pregled.twig',
            compact(
                'karton',
                'takse',
                'takse_nearzduzene',
                'broj_taksi_nearazduzenih',
                'zakupi',
                'zakupi_nerazduzeni',
                'broj_zakupa_nerazduzenih',
                'dug',
                'cena_takse',
                'cena_zakupa',
                'racuni',
                'racuni_nerazduzeni',
                'broj_racuna_nerazduzenih'
            )
        );
    }

    public function getZaduzivanjeTakse($request, $response)
    {
        $model_cene = new Cena();
        $taksa = $model_cene->taksa();

        $this->render($response, 'zaduzivanje_taksi.twig', compact('taksa'));
    }

    public function postZaduzivanjeTakse($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        $iznos = $data['iznos'];
        $godina = $data['godina'];

        $validation_rules = [
            'iznos' => [
                'required' => true,
            ],
            'godina' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        $model_karton = new Karton();
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE godina = :god AND tip = 1;";
        $broj = $model_karton->fetch($sql, [':god' => $godina])[0]->broj;
        if ($broj > 0) {
            $this->flash->addMessage('danger', 'Vec postoji zaduzenje za odabranu godinu');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
        }
        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduzivanja kartona.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
        } else {
            $podaci = [
                ':karton_id' => 0,
                ':tip' => 'taksa',
                ':iznos' => (float) $iznos,
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
                $stmt->execute($podaci);
            }
            $pdo->commit();
            $this->flash->addMessage('success', 'Svi aktivni kartoni su uspesno zaduzeni.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.takse'));
        }
    }

    public function getZaduzivanjeZakup($request, $response)
    {
        $model_cene = new Cena();
        $model_kartoni = new Karton();
        $zakup = $model_cene->zakup();

        // pokupiti sve kartone koji nisu zaduzeni za tekucu godinu
        $tekuca_godina = (int) date('Y');
        $sql = "SELECT * FROM kartoni
                WHERE id NOT IN (
                    SELECT karton_id FROM  zaduzenja
                    WHERE  tip = 2 AND godina = {$tekuca_godina}
                ) AND aktivan = 1 ORDER BY kartoni.id;";
        $nezaduzeni_kartoni = $model_kartoni->fetch($sql);

        $this->render($response, 'zaduzivanje_zakupa.twig', compact('zakup', 'nezaduzeni_kartoni'));
    }

    public function postZaduzivanjeZakup($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        $iznos = $data['iznos'] / 10;
        $godina = $data['godina'];

        $validation_rules = [
            'iznos' => [
                'required' => true,
            ],
            'godina' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduzivanja kartona.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.zakup'));
        } else {
            $model_karton = new Karton();
            $tekuca_godina = (int) date('Y');
            $sql = "SELECT * FROM kartoni
                WHERE id NOT IN (
                    SELECT karton_id FROM  zaduzenja
                    WHERE  tip = 2 AND godina = {$tekuca_godina}
                ) AND aktivan = 1 ORDER BY kartoni.id;";
            $kartoni = $model_karton->fetch($sql);
            $podaci = [
                ':karton_id' => 0,
                ':tip' => 'zakup',
                ':iznos' => (float) $iznos,
                ':godina' => (int) $tekuca_godina,
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
                $podaci[':godina'] = $tekuca_godina + $i;
                foreach ($kartoni as $karton) {
                    $podaci[':karton_id'] = $karton->id;
                    $stmt->execute($podaci);
                }
            }

            $pdo->commit();
            $this->flash->addMessage('success', 'Svi aktivni kartoni su uspesno zaduzeni.');
            return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje.zakup'));
        }
    }

    public function postUplata($request, $response)
    {
        $data = $request->getParams();
        dd($data);
    }
}
