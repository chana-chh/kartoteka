<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Zaduzenje;
use DateTime;

class ZakupController extends Controller
{

    public function getZakup($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);

        $model_cene = new Cena();
        $zakupi = $model_cene->all();
        
        usort($zakupi, function($a, $b) {
        return new DateTime($a->datum) <=> new DateTime($b->datum);
        });

        $this->render($response, 'zakup.twig', compact('zakupi', 'karton'));
    }

    public function postZakup($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $model_cene = new Cena();
        $cena = $model_cene->find($data['zakup_id']);

        $iznos = $cena->zakup / 10;
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
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE godina = :god AND tip = 2;";
        $broj = $model_karton->fetch($sql, [':god' => $godina])[0]->broj;
        if ($broj > 0) {
            $this->flash->addMessage('danger', 'Već postoji zaduženje za odabranu godinu');
            return $response->withRedirect($this->router->pathFor('taksa', ['id' => $data['karton_id']]));
        } else {
            $modelZaduzenja = new Zaduzenje();
                $karton = $modelZaduzenja->insert(
                [
                    'karton_id' => $data['karton_id'],
                    'tip' => 'zakup',
                    'godina' => (int) $godina,
                    'iznos' => (float) $iznos,
                    'razduzeno' => 0,
                    'datum_zaduzenja' =>$data['datum_zaduzenja'],
                    'korisnik_id_zaduzio' => $this->auth->user()->id
                ]
                );

            $this->flash->addMessage('success', 'Kartoni je uspešno zadužen odgovarajućim zakupom.');
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $data['karton_id']]));
        }
    }
    }

    
}
