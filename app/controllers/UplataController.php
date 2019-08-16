<?php

namespace App\Controllers;


use App\Models\Karton;
use App\Models\Uplata;
use DateTime;

class UplataController extends Controller
{

    public function getUplate($request, $response, $args)
    {
        $karton_id = $args['id'];
        $model_karton = new Karton();
        $karton = $model_karton->find($karton_id);
        $uplate = $karton->uplate();

        usort($uplate, function($a, $b) {
        return new DateTime($a->datum) <=> new DateTime($b->datum);
        });

        $this->render($response, 'uplate.twig', compact('karton', 'uplate'));
    }

     public function postUplataBrisanje($request, $response)
    {
        $id = (int)$request->getParam('modal_uplata_id');
        $karton_id = (int) $request->getParam('karton_id');
        $modelUplata = new Uplata();
        $success = $modelUplata->deleteOne($id);
        if ($success) {
            $this->flash->addMessage('success', "Uplata je uspešno obrisana.");
            return $response->withRedirect($this->router->pathFor('uplate', ['id' => $karton_id]));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja uplate.");
            return $response->withRedirect($this->router->pathFor('uplate', ['id' => $karton_id]));
        }
    }

}
