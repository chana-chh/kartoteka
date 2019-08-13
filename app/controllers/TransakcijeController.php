<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Zaduzenje;
use App\Models\Racun;

class TransakcijeController extends Controller
{

    public function getKartonPregled($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);

        $this->render($response, 'transakcije_pregled.twig', compact('karton'));
    }

    public function getKarton($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);

        $this->render($response, 'transakcije_razduzivanje.twig', compact('karton'));
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
