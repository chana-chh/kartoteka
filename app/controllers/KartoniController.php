<?php

namespace App\Controllers;

use App\Models\Karton;

class KartoniController extends Controller
{
    public function getKartoni($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Karton();
        $kartoni = $model->paginate($page);

        $this->render($response, 'kartoni.twig', compact('kartoni'));
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

        $model = new Karton();
        $sql = "SELECT * FROM kartoni WHERE {$where};"; // {$model->getTable()}
        $kartoni = $model->paginate($page, $sql, $params);

        $this->render($response, 'kartoni.twig', compact('kartoni'));
    }
}
