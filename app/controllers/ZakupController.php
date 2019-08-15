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
        $zakupi = $karton->zakupi();

        $model_cene = new Cena();
        $cene = $model_cene->all();
        
        usort($cene, function($a, $b) {
        return new DateTime($a->datum) <=> new DateTime($b->datum);
        });

        usort($zakupi, function($a, $b) {
            if ($a->godina == $b->godina) {
                  return 0;
            } else if ($a->godina > $b->godina) {
                  return 1;
            } else {
                  return -1;
            }
        });

        $this->render($response, 'zakup.twig', compact('cene', 'karton', 'zakupi'));
    }

    public function postZakup($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $model_cene = new Cena();
        $cena = $model_cene->find($data['zakup_id']);

        $model_karton = new Karton();
        $karton = $model_karton->find($data['karton_id']);
        $zakupi = $karton->zakupi();

        $sve_godine = array_column($zakupi, 'godina');
       

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

       if($data['deset'] == 1) {
            $model_karton = new Karton();
            $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE karton_id = :kar AND godina = :god AND tip = 2;";
            $broj = $model_karton->fetch($sql, [':god' => $godina, ':kar' => $data['karton_id']])[0]->broj;
            if ($broj > 0) {
                $this->flash->addMessage('danger', 'Već postoji zaduženje za odabranu godinu');
                return $response->withRedirect($this->router->pathFor('zakup', ['id' => $data['karton_id']]));
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
       }else{

            $godine_request = [];
            for ($i = 0; $i < 10; $i++) {
                array_push($godine_request, $godina + $i);
                }
            $razlike = array_diff($godine_request, $sve_godine);
            $duzina_niza = count($razlike);

            if ($duzina_niza > 0) {
                foreach ($razlike as $godina) {
                    $modelZaduzenja = new Zaduzenje();
                    $karton = $modelZaduzenja->insert(
                    [
                        'karton_id' => $data['karton_id'],
                        'tip' => 'zakup',
                        'godina' => $godina,
                        'iznos' => (float) $iznos,
                        'razduzeno' => 0,
                        'datum_zaduzenja' =>$data['datum_zaduzenja'],
                        'korisnik_id_zaduzio' => $this->auth->user()->id
                    ]
                    );
                }
            }else{
                $this->flash->addMessage('danger', 'Kartoni je već zadužen za zadati period odgovarajućim zakupom.');
                return $response->withRedirect($this->router->pathFor('zakup', ['id' => $data['karton_id']]));
            }
            
            $this->flash->addMessage('success', 'Kartoni je uspešno zadužen za zadati period odgovarajućim zakupom.');
            return $response->withRedirect($this->router->pathFor('zakup', ['id' => $data['karton_id']]));
       }
    }
    }

    
}
