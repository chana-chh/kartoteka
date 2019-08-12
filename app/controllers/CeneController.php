<?php

namespace App\Controllers;

use App\Models\Cena;

class CeneController extends Controller
{
    public function getCene($request, $response)
    {
        $model = new Cena();
        $cene = $model->all('datum', 'DESC'); //Ovo ne` da sortira
        $taksa = $model->taksa();
        $zakup = $model->zakup();

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
            return $response->withRedirect($this->router->pathFor('groblja'));
        } else {
            $modelCena = new Cena();
            $modelCena->update($data, $id);
            $id_vazece = $modelCena->odrediVazece()->id;

            $modelR = new Cena();
            $dataU['vazece'] = 1;
            $modelR->update($dataU, $id_vazece);
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
            return $response->withRedirect($this->router->pathFor('groblja'));
        } else {
            $modelCena = new Cena();
            $modelCena->insert($data);
            $id_vazece = $modelCena->odrediVazece()->id;

            $modelR = new Cena();
            $dataU['vazece'] = 1;
            $modelR->update($dataU, $id_vazece);
            $this->flash->addMessage('success', 'Nove cene su uspešno dodate.');
            return $response->withRedirect($this->router->pathFor('cene'));
        }
    }

}
