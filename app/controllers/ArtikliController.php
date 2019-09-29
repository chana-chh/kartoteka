<?php

namespace App\Controllers;

use App\Models\Artikal;

class ArtikliController extends Controller
{
    public function getArtikli($request, $response)
    {
        $model = new Artikal();
        $artikli = $model->all();

        $this->render($response, 'artikli.twig', compact('artikli'));
    }

    // public function getPoreziIzmena($request, $response, $args)
    // {
    //     $id = (int) $args['id'];
    //     $modelPorez = new Porez();
    //     $porez = $modelPorez->find($id);
    //     $this->render($response, 'porezi_izmena.twig', compact('porez'));
    // }

    // public function postporeziIzmena($request, $response)
    // {
    //     $data = $request->getParams();
    //     $id = $data['id'];
    //     unset($data['id']);
    //     unset($data['csrf_name']);
    //     unset($data['csrf_value']);

    //     $validation_rules = [
    //         'datum' => [
    //             'required' => true
    //         ],
    //         'taksa' => [
    //             'required' => true
    //         ],
    //     ];

    //     $this->validator->validate($data, $validation_rules);

    //     if ($this->validator->hasErrors()) {
    //         $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene poreza.');
    //         return $response->withRedirect($this->router->pathFor('porezi'));
    //     } else {
    //         $modelPorez = new Porez();
    //         $modelPorez->update($data, $id);

    //         $this->flash->addMessage('success', 'Porez je uspešno izmenjen.');
    //         return $response->withRedirect($this->router->pathFor('porezi'));
    //     }
    // }

    // public function getPoreziDodavanje($request, $response)
    // {
    //     $this->render($response, 'porezi_dodavanje.twig');
    // }

    // public function postPoreziDodavanje($request, $response)
    // {
    //     $data = $request->getParams();
    //     unset($data['csrf_name']);
    //     unset($data['csrf_value']);

    //     $validation_rules = [
    //         'naziv' => [
    //             'required' => true
    //         ],
    //         'procenat' => [
    //             'required' => true
    //         ],
    //     ];

    //     $this->validator->validate($data, $validation_rules);

    //     if ($this->validator->hasErrors()) {
    //         $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja poreza.');
    //         return $response->withRedirect($this->router->pathFor('cene'));
    //     } else {
    //         $modelPorez = new Porez();
    //         $modelPorez->insert($data);

    //         $this->flash->addMessage('success', 'Novi porez je uspešno dodat.');
    //         return $response->withRedirect($this->router->pathFor('porezi'));
    //     }
    // }

    // public function postPoreziBrisanje($request, $response)
    // {
    //     $id = (int) $request->getParam('modal_porez_id');
    //     $modelPorez = new Porez();
    //     $success = $modelPorez->deleteOne($id);
    //     if ($success) {
    //         $this->flash->addMessage('success', "Porez je uspešno obrisan.");
    //         return $response->withRedirect($this->router->pathFor('porezi'));
    //     } else {
    //         $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja poreza.");
    //         return $response->withRedirect($this->router->pathFor('porezi'));
    //     }
    // }
}
