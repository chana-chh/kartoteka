<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Groblje;
use App\Models\Mapa;

class KartoniController extends Controller
{
    public function getKartoni($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $modelKarton = new Karton();
        $modelGroblje = new Groblje();
        $kartoni = $modelKarton->paginate($page);
        $groblja = $modelGroblje->all();

        $this->render($response, 'kartoni.twig', compact('kartoni', 'groblja'));
    }

    public function postKartoniPretraga($request, $response)
    {
        $_SESSION['DATA_KARTONI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('kartoni.pretraga'));
    }

    public function getKartoniPretraga($request, $response)
    {
        $data = $_SESSION['DATA_KARTONI_PRETRAGA'];
        // unset($_SESSION['DATA']);
        array_shift($data);
        array_shift($data);
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = "groblje_id = :groblje_id";
        $params = [':groblje_id' => $data['groblje_id']];
        if (!empty($data['parcela'])) {
            $where .= " AND parcela = :parcela";
            $params[':parcela'] = $data['parcela'];
        }
        if (!empty($data['grobno_mesto'])) {
            $where .= " AND grobno_mesto = :grobno_mesto";
            $params[':grobno_mesto'] = $data['grobno_mesto'];
        }
        $modelGroblje = new Groblje();
        $groblja = $modelGroblje->all();

        $model = new Karton();
        $sql = "SELECT * FROM {$model->getTable()} WHERE {$where};";
        $kartoni = $model->paginate($page, $sql, $params);

        $this->render($response, 'kartoni.twig', compact('kartoni', 'groblja', 'data'));
    }

    public function getKartoniPregled($request, $response, $args)
    {
        $id = $args['id'];
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id);
        $saldo = $karton->saldo();
        $this->render($response, 'karton_pregled.twig', compact('karton', 'saldo'));
    }

    public function getKartoniMapa($request, $response, $args)
    {
        $id = $args['id'];
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id);
        $grobno_mesto = $karton->grobno_mesto;
        $modelMapa = new Mapa();
        $mapaM = $modelMapa->pronadjiMapu($karton->groblje_id, $karton->parcela);
        $mapa = (string)($mapaM->veza);
        $this->render($response, 'karton_mapa.twig', compact('karton', 'grobno_mesto', 'mapa'));
    }

     public function postKartoniMapa($request, $response)
     {
        $modelKarton = new Karton();
        $id_kartona = $request->getParam('id_kartona');
        $karton = $modelKarton->update([
            'x_pozicija' => $request->getParam('x_pozicija'), 
            'y_pozicija' => $request->getParam('y_pozicija')], 
            $id_kartona);

        $this->flash->addMessage('success', 'Koordinate za mapu su uspesno dodati/izmenjene.');
        return $response->withRedirect($this->router->pathFor('kartoni.mapa', ['id' => $id_kartona]));
     }
}
