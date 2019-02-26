<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Groblje;
use App\Models\Mapa;

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

        $this->render($response, 'mape.twig', compact('mape', 'groblja'));
    }

    public function postUpload($request, $response)
    {
        $uploadedFiles = $request->getUploadedFiles();
    }

    public function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}
}
