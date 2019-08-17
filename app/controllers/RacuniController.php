<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Racun;

class RacuniController extends Controller
{
    public function getRacun($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);

        $this->render($response, 'racun.twig', compact('karton'));
    }

    public function postRacun($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $validation_rules = [
            'karton_id' => [
                'required' => true,
            ],
            'datum' => [
                'required' => true,
            ],
            'iznos' => [
                'required' => true,
                'min' => 0.01,
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduženja računa.');
            return $response->withRedirect($this->router->pathFor('racun', ['id' => $data['karton_id']]));
        } else {
            $data['razduzeno'] = 0;
            $data['korisnik_id_zaduzio'] = $this->auth->user()->id;
            $model = new Racun();
            $model->insert($data);
            $this->flash->addMessage('success', 'Karton je uspešno zadužen računom.');
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $data['karton_id']]));
        }
    }

        public function postRacunBrisanje($request, $response)
    {
        $id = (int) $request->getParam('modal_racun_id');
        $karton_id = (int) $request->getParam('karton_id');
        $model = new Racun();
        $success = $model->deleteOne($id);
        if ($success) {
            $this->flash->addMessage('success', "Račun je uspešno obrisano.");
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja računa.");
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
        }
    }
}
