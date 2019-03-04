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
        $this->render($response, 'dokument_dodavanje.twig', compact('karton', 'groblja', 'tipovi'));
    }

    public function postDokumentiDodavanje($request, $response)
    {
        $id_kartona = (int)$request->getParam('karton_id');
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        $dokument = $request->getUploadedFiles()['dokument'];
        if ($dokument->getError() === UPLOAD_ERR_NO_FILE) {
            $this->flash->addMessage('danger', 'Morate odabrati dokument.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
        if ($dokument->getError() !== UPLOAD_ERR_OK) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom prebacivanja dokumenta.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }

        $validation_rules = [
            'tip' => [
                'required' => true,
                'multi_unique' => 'dokumenta.karton_id,tip,datum,opis',
            ],
            'datum' => [
                'required' => true,
            ],
            'opis' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja dokumenta.');
            return $response->withRedirect($this->router->pathFor('dokumenti.dodavanje',['id'=>$id_kartona]));
        } else {
            $unique = bin2hex(random_bytes(8));
            $extension = pathinfo($dokument->getClientFilename(), PATHINFO_EXTENSION);
            $name = "{$id_kartona}_{$data['tip']}_{$data['datum']}_{$unique}";
            $filename = "{$name}.{$extension}";
            $uri = $request->getUri();
            $veza = $uri->getScheme() . "://" . $uri->getHost() . $uri->getBasePath() . "/doc/{$filename}";
            $data['veza'] = $veza;
            $dokument->moveTo('doc/' . $filename);
            $modelDokument = new Dokument();
            $modelDokument->insert($data);
            $this->flash->addMessage('success', 'Dokument je uspešno sačuvan.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
    }
}
