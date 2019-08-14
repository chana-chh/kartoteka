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

        $model_cene = new Cena();
        $takse = $model_cene->all();

        usort($takse, function($a, $b) {
        return new DateTime($a->datum) <=> new DateTime($b->datum);
        });

        $this->render($response, 'taksa.twig', compact('takse', 'karton'));
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
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE godina = :god AND tip = 1;";
        $broj = $model_karton->fetch($sql, [':god' => $godina])[0]->broj;
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
