<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Groblje;

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

        $this->render($response, 'kartoni.twig', compact('kartoni', 'groblja'));
    }
}
