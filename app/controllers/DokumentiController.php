<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Dokument;

class DokumentiController extends Controller
{
    public function getDokumentiDodavanje($request, $response, $args)
    {
        $id_kartona = (int)$args['id'];
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id_kartona);
        $modelDokument = new Dokument();
        $tipovi = $modelDokument->enumOrSetList('tip');
        dd($tipovi);
        $this->render($response, 'dokument_dodavanje.twig', compact('karton', 'groblja', 'tipovi'));
    }

    public function postDokumentiDodavanje($request, $response)
    {
        $id_kartona = (int)$request->getParam('dok_dodavanje_id');
        $dokument = $request->getUploadedFiles()['dokument'];
        if ($dokument->getError() === UPLOAD_ERR_NO_FILE) {
            $this->flash->addMessage('danger', 'Morate odabrati dokument.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
        if ($dokument->getError() !== UPLOAD_ERR_OK) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom prebacivanja dokumenta.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
        dd($dokument);

        $extension = pathinfo($dokument->getClientFilename(), PATHINFO_EXTENSION);
        $filename = $request->getParam('groblje_id') . '-' . $request->getParam('parcela') . '.' . $extension;
        $dokument->moveTo('doc/' . $filename);

        $modelMape = new Mapa();
        $karton = $modelMape->insert(
            [
                'groblje_id' => $request->getParam('groblje_id'),
                'parcela' => $request->getParam('parcela'),
                'veza' => $filename
            ]
        );

        $this->flash->addMessage('success', 'Dokument je uspešno sačuvan.');
        return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
    }
}
