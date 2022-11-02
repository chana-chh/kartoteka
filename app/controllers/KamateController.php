<?php

namespace App\Controllers;

use App\Models\Kamata;

class KamateController extends Controller
{
    public function getKamate($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Kamata();
        $sql = "SELECT * FROM {$model->getTable()} ORDER BY datum DESC;";
        $kamate = $model->paginate($page, $sql);

        $this->render($response, 'kamate.twig', compact('kamate'));
    }

    public function postKamateDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        if (date('L', strtotime($data['datum']))) {
            $data['dani'] = 366;
         }else{
            $data['dani'] = 365;
         }

        $validation_rules = [
            'datum' => [
                'required' => true
            ],
            'procenat' => [
                'required' => true
            ],
            'dani' => [
                'required' => true
            ]
        ];
        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja kamate.');
            return $response->withRedirect($this->router->pathFor('kamate'));
        } else {
            $this->flash->addMessage('success', 'Kamata je uspešno dodata.');
            $modelKamate = new Kamata();
            $modelKamate->insert($data);
            $id = $modelKamate->getLastId();
            $kamata = $modelKamate->find($id);
            $this->log($this::DODAVANJE, $kamata, ['procenat', 'datum'], $kamata);
            return $response->withRedirect($this->router->pathFor('kamate'));
        }
    }

    public function postKamateBrisanje($request, $response)
    {
		if ($this->auth->user()->nivo !== 0)
		{
			$this->flash->addMessage('danger', "Samo administrator može da obriše kamatu.");
            return $response->withRedirect($this->router->pathFor('kamate'));
		}

        $id = (int)$request->getParam('modal_kamata_id');
        $modelKamate = new Kamata();
        $kamata = $modelKamate->find($id);
        $success = $modelKamate->deleteOne($id);
        if ($success) {
            $this->flash->addMessage('success', "Kamata je uspešno obrisano.");
            $this->log($this::BRISANJE, $kamata, ['procenat', 'datum'], $kamata);
            return $response->withRedirect($this->router->pathFor('kamate'));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja kamate.");
            return $response->withRedirect($this->router->pathFor('kamate'));
        }
    }

    public function getKamateIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelKamate = new Kamata();
        $kamata = $modelKamate->find($id);
        $this->render($response, 'kamate_izmena.twig', compact('kamata'));
    }

     public function postKamateIzmena($request, $response)
    {
        $data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        if (date('L', strtotime($data['datum']))) {
            $data['dani'] = 366;
         }else{
            $data['dani'] = 365;
         }

        $validation_rules = [
            'datum' => [
                'required' => true
            ],
            'procenat' => [
                'required' => true
            ],
            'dani' => [
                'required' => true
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene podataka kamate.');
            return $response->withRedirect($this->router->pathFor('kamate.izmena', ['id' => $id]));
        } else {
            $this->flash->addMessage('success', 'Podaci o kamati su uspešno izmenjeni.');
            $modelKamate = new Kamata();
            $stari = $modelKamate->find($id);
            $modelKamate->update($data, $id);
            $kamata = $modelKamate->find($id);
            $this->log($this::IZMENA, $kamata, ['procenat', 'datum'], $stari);
            return $response->withRedirect($this->router->pathFor('kamate'));
        }
    }
}
