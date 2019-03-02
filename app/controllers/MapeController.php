<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Groblje;
use App\Models\Mapa;

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
        $uploadedFiles = $request->getUploadedFiles();

        $uploadedFile = $uploadedFiles['slika'];

        if ($uploadedFile->getError() != UPLOAD_ERR_OK) {
        $this->flash->addMessage('danger', 'Došlo je do greške prilikom prebacivanja mape.');
        return $response->withRedirect($this->router->pathFor('mape'));
     }
        else {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filename = $request->getParam('groblje').''.$request->getParam('parcela').'.'.$extension;
        $filenameThumb = $request->getParam('groblje').''.$request->getParam('parcela');

        $basePath = $request->getUri()->getBasePath();

        $uploadedFile->moveTo('img/Mape/' . $filename);

        $targetFile = 'img/Mape/Thumb/'.$filenameThumb;
        $originalFile = 'img/Mape/'.$filename;

        $this->resize(200, $targetFile, $originalFile);


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
                    throw new Exception('Unknown image type.');
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

}
