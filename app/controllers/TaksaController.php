<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Zaduzenje;
use DateTime;

class TaksaController extends Controller
{

    public function getTaksa($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);
        $takse = $karton->takse();

        $model_cene = new Cena();
        $cene = $model_cene->all();

        usort($cene, function($a, $b) {
        return new DateTime($a->datum) <=> new DateTime($b->datum);
        });

        usort($takse, function($a, $b) {
            if ($a->godina == $b->godina) {
                  return 0;
            } else if ($a->godina > $b->godina) {
                  return 1;
            } else {
                  return -1;
            }
        });

        $this->render($response, 'taksa.twig', compact('cene', 'karton', 'takse'));
    }

    public function postTaksa($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $model_cene = new Cena();
        $cena = $model_cene->find($data['taksa_id']);

        $iznos = $cena->taksa;
        $godina = $cena->godina();


        $model_karton = new Karton();
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE karton_id = :kar AND godina = :god AND tip = 1;";
        $broj = $model_karton->fetch($sql, [':god' => $godina, ':kar' => $data['karton_id']])[0]->broj;
        if ($broj > 0) {
            $this->flash->addMessage('danger', 'Već postoji zaduženje za odabranu godinu');
            return $response->withRedirect($this->router->pathFor('taksa', ['id' => $data['karton_id']]));
        } else {
            $modelZaduzenja = new Zaduzenje();
                $karton = $modelZaduzenja->insert(
                [
                    'karton_id' => $data['karton_id'],
                    'tip' => 'taksa',
                    'godina' => (int) $godina,
                    'iznos' => (float) $iznos,
                    'razduzeno' => 0,
                    'datum_zaduzenja' =>$data['datum_zaduzenja'],
                    'korisnik_id_zaduzio' => $this->auth->user()->id
                ]
                );

            $this->flash->addMessage('success', 'Kartoni je uspešno zadužen odgovarajućom taksom.');
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $data['karton_id']]));
        }
    }

    
}
