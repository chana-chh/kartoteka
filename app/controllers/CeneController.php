<?php

namespace App\Controllers;

use App\Models\Cena;
use DateTime;

class CeneController extends Controller
{
    public function getCene($request, $response)
    {
        $model = new Cena();
        $cene = $model->all();
        $taksa = $model->taksa();
        $zakup = $model->zakup();

        usort($cene, function($a, $b) {
        return new DateTime($a->datum) <=> new DateTime($b->datum);
        });
        $this->render($response, 'cene.twig', compact('cene', 'taksa', 'zakup'));
    }

    public function getCeneIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelCena = new Cena();
        $cene = $modelCena->find($id);
        $this->render($response, 'cene_izmena.twig', compact('cene'));
    }

    public function postCeneIzmena($request, $response)
    {
        $data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

         $validation_rules = [
            'datum' => [
                'required' => true
            ],
            'taksa' => [
                'required' => true
            ],
            'zakup' => [
                'required' => true,
            ]
        ];
        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene cena.');
            return $response->withRedirect($this->router->pathFor('cene'));
        } else {
            $modelCena = new Cena();
            $stari = $modelCena->find($id);
            $modelCena->update($data, $id);
            $modelCena = $modelCena->find($id);
            $modelCena->odrediVazece();
            $this->log($this::IZMENA, $modelCena, ['datum', 'taksa', 'zakup'], $stari);
            $this->flash->addMessage('success', 'Cene su uspešno izmenjene.');
            return $response->withRedirect($this->router->pathFor('cene'));
        }
    }

     public function getCeneDodavanje($request, $response)
    {
        $this->render($response, 'cene_dodavanje.twig');
    }

    public function postCeneDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $validation_rules = [
            'datum' => [
                'required' => true
            ],
            'taksa' => [
                'required' => true
            ],
            'zakup' => [
                'required' => true,
            ]
        ];
        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja cena.');
            return $response->withRedirect($this->router->pathFor('cene'));
        } else {
            $modelCena = new Cena();
            $modelCena->insert($data);
            $id = $modelCena->getLastId();
            $modelCena->odrediVazece();
            $cena = $modelCena->find($id);
            $this->log($this::DODAVANJE, $cena, 'datum', $cena);

            $this->flash->addMessage('success', 'Nove cene su uspešno dodate.');
            return $response->withRedirect($this->router->pathFor('cene'));
        }
    }

    public function postCeneBrisanje($request, $response)
    {
        if($this->auth->user()->nivo !== 0)
        {
            return $response->withRedirect($this->router->pathFor('cene'));
        }

        $id = (int)$request->getParam('modal_cena_id');
        $modelCena = new Cena();
        $cena = $modelCena->find($id);
        if ($cena->vazece == 1) {
            $this->flash->addMessage('danger', "Nije moguće obrisati trenutno važeće cene.");
            return $response->withRedirect($this->router->pathFor('cene'));
        }
        $success = $modelCena->deleteOne($id);
        if ($success) {
            $this->log($this::BRISANJE, $cena, 'datum', $cena);
            $modelCena->odrediVazece();
            $this->flash->addMessage('success', "Cene su uspešno obrisane.");
            return $response->withRedirect($this->router->pathFor('cene'));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja cena.");
            return $response->withRedirect($this->router->pathFor('cene'));
        }
    }

}
