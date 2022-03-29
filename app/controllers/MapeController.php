<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Groblje;
use App\Models\Mapa;
use App\Models\Log;

use \Exception;

use Slim\Http\UploadedFile;


class MapeController extends Controller
{
    public function getMape($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $modelMapa = new Mapa();
        $mape = $modelMapa->paginate($page);

         $modelGroblje = new Groblje();
         $groblja = $modelGroblje->all();

         $modelKarton = new Karton();
         $parcele = $modelKarton->vratiParcele();

        $this->render($response, 'mape.twig', compact('mape', 'groblja', 'parcele'));
    }

    public function postUpload($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        //Validacija

        $validation_rules = [
            'groblje_id' => [
                'required' => true,
                'multi_unique' => 'mape.groblje_id,parcela'
            ]
        ];

        if (!isset($data['parcela'])) {
            $this->flash->addMessage('danger', 'Morate odabrati bar jednu parcelu sa liste.');
            return $response->withRedirect($this->router->pathFor('mape'));
        }

        foreach ($data['parcela'] as $parcela) {
            $this->validator->validate(['groblje_id' => $data['groblje_id'], 'parcela' => $parcela], $validation_rules);

            if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja nove mape. Podaci nisu validni. Problematična parcela:  '.$parcela);
            return $response->withRedirect($this->router->pathFor('mape'));
        }}

        //Kraj validacije

            $uploadedFiles = $request->getUploadedFiles();
            $uploadedFile = $uploadedFiles['slika'];

        if ($uploadedFile->getError() != UPLOAD_ERR_OK) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja nove mape. Slika/mapa nije izabrana.');
            return $response->withRedirect($this->router->pathFor('mape'));
        }
        else {
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $filename = $request->getParam('groblje_id').'_'.implode("_",$data['parcela']).'.'.$extension;
            $filenameThumb = $request->getParam('groblje_id').'_'.implode("_",$data['parcela']);

            $uploadedFile->moveTo('img/Mape/' . $filename);

            $targetFile = 'img/Mape/Thumb/'.$filenameThumb;
            $originalFile = 'img/Mape/'.$filename;

            $this->resize(200, $targetFile, $originalFile);

            foreach ($data['parcela'] as $parcela) {
                $modelMape = new Mapa();
                $karton = $modelMape->insert(
                [
                    'groblje_id' => $data['groblje_id'],
                    'parcela' => $parcela,
                    'veza' => $filename,
                    'opis_mape' => $data['opis']
                ]
                );
            }
            //LOG Ručno
            $modelGroblja = new Groblje();
            $groblje = $modelGroblja->find($data['groblje_id']);
            $modelLoga = new Log();
            $datal['tip'] = "dodavanje";
            $datal['opis'] = "Mapa: " . $groblje->naziv . ", " . $data['opis'];
            $datal['izmene'] = "Parcele: " . implode(",", $data['parcela']);
            $datal['korisnik_id'] = $this->auth->user()->id;
            $modelLoga->insert($datal);
            $this->flash->addMessage('success', 'Mapa '. $filename. ' je uspešno sačuvana');
            return $response->withRedirect($this->router->pathFor('mape'));
            }
    }

    private function resize($newWidth, $targetFile, $originalFile) {

    $info = getimagesize($originalFile);
    $mime = $info['mime'];

        switch ($mime) {
                    case 'image/jpeg':
                            $image_create_func = 'imagecreatefromjpeg';
                            $image_save_func = 'imagejpeg';
                            $new_image_ext = 'jpg';
                            break;

                    case 'image/png':
                            $image_create_func = 'imagecreatefrompng';
                            $image_save_func = 'imagepng';
                            $new_image_ext = 'png';
                            break;

                    case 'image/gif':
                            $image_create_func = 'imagecreatefromgif';
                            $image_save_func = 'imagegif';
                            $new_image_ext = 'gif';
                            break;

                    default: 
                            throw new Exception('Grafički format nije podržan.');
                    }

            $img = $image_create_func($originalFile);
            list($width, $height) = getimagesize($originalFile);

            $newHeight = ($height / $width) * $newWidth;
            $tmp = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            if (file_exists($targetFile)) {
                    unlink($targetFile);
            }
            $image_save_func($tmp, "$targetFile.$new_image_ext");
    }

    public function postMapaBrisanje($request, $response)
    {
        $id = (int)$request->getParam('brisanje_id');
        $modelMape = new Mapa();
        $mapa = $modelMape->find($id);
        $veza = $modelMape->find($id)->veza;
        $broj = count($modelMape->isteMape($veza));
        $success = $modelMape->deleteOne($id);


        if ($broj < 1) {
            $thumb = 'img/Mape/Thumb/'.$veza;  

            if (file_exists($thumb)) {
                    $success_thumb = unlink($thumb);
            }

            $mapa = 'img/Mape/'.$veza;

            if (file_exists($mapa)) {
                    $success_mapa = unlink($mapa);
            }
        }

        if ($success and $success_thumb and $success_mapa) {
            $this->log($this::BRISANJE, $mapa, 'opis', $mapa);
            $this->flash->addMessage('success', "Mapa sa nazivom [{$veza}] je uspešno obrisana.");
            return $response->withRedirect($this->router->pathFor('mape'));
        } elseif($success and !$success_thumb and !$success_thumb) {
            $this->log($this::BRISANJE, $mapa, 'opis', $mapa);
            $this->flash->addMessage('warning', "Mapa sa nazivom [{$veza}] je uspešno obrisana, ali samo iz baze.");
            return $response->withRedirect($this->router->pathFor('mape'));
        }else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja mape.");
            return $response->withRedirect($this->router->pathFor('mape'));
        }
    }

    public function getMapaPovezi($request, $response, $args){

        $id = (int)$args['id'];
        $modelMape = new Mapa();
        $sveMape = $modelMape->all();
        $mapa = $modelMape->find($id);
        $modelKarton = new Karton();
        $parcele = $modelKarton->vratiParcele();
        $sve = array_column($parcele, 'parcela');
        $izmapa = array_column($sveMape, 'parcela');
        $razlike = array_diff($sve, $izmapa);

        $ostale = $modelMape->isteMape($mapa->veza);

        $this->render($response, 'mapa_povezi.twig', compact('mapa', 'razlike', 'ostale'));


    }

    public function postMapaPovezi($request, $response){

        $data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $modelMapePolaz = new Mapa();
        $mapapolaz = $modelMapePolaz->find($id);
        $veza = $mapapolaz->veza;

        $validation_rules = [
            'groblje_id' => [
                'required' => true,
                'multi_unique' => 'mape.groblje_id,parcela'
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom pokušaja da se poveže dodatna parcela za mapu!');
            return $response->withRedirect($this->router->pathFor('mapa.povezi.get', ['id' => $id]));
        } else {
            $modelMape = new Mapa();
            $data['veza'] = $veza;
            $data['opis_mape'] = $mapapolaz->opis_mape;
            $modelMape->insert($data);
            $id = $modelMape->getLastId();
            $mapa = $modelMape->find($id);
            $this->log($this::DODAVANJE, $mapa, ['groblje_id', 'parcela'], $mapa);
            $this->flash->addMessage('success', 'Uspešno ste povezali parcelu sa postojećom mapom.');
            return $response->withRedirect($this->router->pathFor('mape'));
        }


    }

    public function getMapaIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelMape = new Mapa();
        $mapa = $modelMape->find($id);
        $modelGroblje = new Groblje();
        $groblja = $modelGroblje->all();
        $modelKarton = new Karton();
        $parcele = $modelKarton->vratiParcele();

        $ostale = $modelMape->isteMape($mapa->veza);

        $this->render($response, 'mapa_izmena.twig', compact('mapa', 'groblja', 'parcele', 'ostale'));
    }

    public function postMapaIzmena($request, $response)
    {
        $data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

       $validation_rules = [
            'groblje_id' => [
                'required' => true,
                'multi_unique' => 'mape.groblje_id,parcela#id:' . $id
            ]
        ];

        if (!isset($data['parcela'])) {
            $this->flash->addMessage('danger', 'Morate odabrati bar jednu parcelu sa liste.');
            return $response->withRedirect($this->router->pathFor('mapa.izmena', ['id' => $id]));
        }

        $this->validator->validate(['groblje_id' => $data['groblje_id'], 'parcela' => $data['parcela']], $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene mape. Podaci nisu validni. Problematična parcela:  '.$data['parcela']);
            return $response->withRedirect($this->router->pathFor('mapa.izmena', ['id' => $id]));
        } else {
            $this->flash->addMessage('success', 'Podaci o mapi su uspešno izmenjeni.');
            $modelMape = new Mapa();
            $modelMape->update($data, $id);
            return $response->withRedirect($this->router->pathFor('mape'));
        }
    }

}
