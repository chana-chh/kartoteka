<?php

namespace App\Controllers;

use App\Models\Groblje;

class GrobljaController extends Controller
{
    public function getGroblja($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Groblje();
        $sql = "SELECT * FROM {$model->getTable()} ORDER BY naziv;";
        $groblja = $model->paginate($page, $sql);

        $this->render($response, 'groblja.twig', compact('groblja'));
    }

    public function postGrobljaDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 190,
                'alnum' => true,
                'unique' => 'groblja.naziv',
            ],
            'adresa' => [
                'required' => true,
                'maxlen' => 255,
                'alnum' => true,
            ],
            'mesto' => [
                'required' => true,
                'maxlen' => 190,
            ],
            'ptt' => [
                'required' => true,
            ]
        ];
        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja groblja.');
            return $response->withRedirect($this->router->pathFor('groblja'));
        } else {
            $this->flash->addMessage('success', 'Novo groblje je uspešno dodato.');
            $modelGroblja = new Groblje();
            $modelGroblja->insert($data);
            $id = $modelGroblja->getLastId();
            $groblje = $modelGroblja->find($id);
            $this->log($this::DODAVANJE, $groblje, 'naziv', $groblje);
            return $response->withRedirect($this->router->pathFor('groblja'));
        }
    }

    public function postGrobljaBrisanje($request, $response)
    {
        $id = (int)$request->getParam('modal_groblje_id');
        $modelGroblja = new Groblje();
        $groblje = $modelGroblja->find($id);
        $success = $modelGroblja->deleteOne($id);
        if ($success) {
            $this->flash->addMessage('success', "Groblje je uspešno obrisano.");
            $this->log($this::BRISANJE, $groblje, 'naziv', $groblje);
            return $response->withRedirect($this->router->pathFor('groblja'));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja groblja.");
            return $response->withRedirect($this->router->pathFor('groblja'));
        }
    }

    public function getGrobljaIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelGroblja = new Groblje();
        $groblje = $modelGroblja->find($id);
        $this->render($response, 'groblja_izmena.twig', compact('groblje'));
    }

     public function postGrobljaIzmena($request, $response)
    {
        $data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $validation_rules = [
            'naziv' => [
                'required' => true,
                'minlen' => 5,
                'maxlen' => 190,
                'alnum' => true,
                'unique' => 'groblja.naziv#id:' . $id,
            ],
            'adresa' => [
                'required' => true,
                'maxlen' => 255,
                'alnum' => true,
            ],
            'mesto' => [
                'required' => true,
                'maxlen' => 190,
            ],
            'ptt' => [
                'required' => true,
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene podataka groblja.');
            return $response->withRedirect($this->router->pathFor('groblja.izmena', ['id' => $id]));
        } else {
            $this->flash->addMessage('success', 'Podaci o groblju su uspešno izmenjeni.');
            $modelGroblja = new Groblje();
            $stari = $modelGroblja->find($id);
            $modelGroblja->update($data, $id);
            $groblje = $modelGroblja->find($id);
            $this->log($this::IZMENA, $groblje, 'naziv', $stari);
            return $response->withRedirect($this->router->pathFor('groblja'));
        }
    }
}
