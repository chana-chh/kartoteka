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
            return $response->withRedirect($this->router->pathFor('dokumenti.dodavanje', ['id' => $id_kartona]));
        } else {
            $unique = bin2hex(random_bytes(8));
            $extension = pathinfo($dokument->getClientFilename(), PATHINFO_EXTENSION);
            $name = "{$id_kartona}_{$data['tip']}_{$data['datum']}_{$unique}";
            $filename = "{$name}.{$extension}";
            $veza = URL . "/doc/{$filename}";
            $data['veza'] = $veza;
            $dokument->moveTo('doc/' . $filename);
            $modelDokument = new Dokument();
            $modelDokument->insert($data);
            $this->flash->addMessage('success', 'Dokument je uspešno sačuvan.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
    }

    public function postDokumentiBrisanje($request, $response)
    {
        $id = (int)$request->getParam('modal_dokument_id');
        $karton_id = (int)$request->getParam('modal_dokument_karton_id');
        $modelDokument = new Dokument();
        $dok = $modelDokument->find($id);
        $tmp = explode('/', $dok->veza);
        $file = DIR . 'pub' . DS . 'doc' . DS . end($tmp);
        $success = $modelDokument->deleteOne($id);
        if ($success) {
            unlink($file);
            $this->flash->addMessage('success', "Dokument je uspešno obrisan.");
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $karton_id]));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja dokumenta.");
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $karton_id]));
        }
    }

    public function getDokumentiIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelDokument = new Dokument();
        $dokument = $modelDokument->find($id);
        $tipovi = $modelDokument->enumOrSetList('tip');
        $this->render($response, 'dokument_izmena.twig', compact('dokument', 'tipovi'));
    }

    public function postDokumentiIzmena($request, $response)
    {
        $id = (int)$request->getParam('id');
        $id_kartona = (int)$request->getParam('karton_id');
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        unset($data['id']);
        unset($data['karton_id']);
        $dokument = $request->getUploadedFiles()['dokument'];
        $novi_dok = ($dokument->getError() === UPLOAD_ERR_NO_FILE) ? false : true;
        if ($novi_dok && $dokument->getError() !== UPLOAD_ERR_OK) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom prebacivanja novog dokumenta.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
        $validation_rules = [
            'tip' => [
                'required' => true,
                'multi_unique' => 'dokumenta.karton_id,tip,datum,opis#id:' . $id,
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
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene dokumenta.');
            return $response->withRedirect($this->router->pathFor('dokumenti.izmena', ['id' => $id_kartona]));
        } else {
            $modelDokument = new Dokument();
            $dok = $modelDokument->find($id);
            if ($novi_dok) {
                $tmp = explode('/', $dok->veza);
                $file = DIR . 'pub' . DS . 'doc' . DS . end($tmp);
                unlink($file);
                $unique = bin2hex(random_bytes(8));
                $extension = pathinfo($dokument->getClientFilename(), PATHINFO_EXTENSION);
                $name = "{$id_kartona}_{$data['tip']}_{$data['datum']}_{$unique}";
                $filename = "{$name}.{$extension}";
                $veza = URL . "/doc/{$filename}";
                $data['veza'] = $veza;
                $dokument->moveTo('doc/' . $filename);
            }
            $modelDokument->update($data, $id);
            $this->flash->addMessage('success', 'Izmene dokumenta su uspešno sačuvane.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
    }
}
