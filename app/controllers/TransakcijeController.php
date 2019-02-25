<?php

namespace App\Controllers;

use App\Models\Transakcija;
use App\Models\Karton;
use App\Models\TipTransakcije;

class TransakcijeController extends Controller
{
    public function getTransakcijeKarton($request, $response, $args)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $karton_id = $args['id'];
        $mk = new Karton();
        $karton = $mk->find($karton_id);
        $saldo = $karton->saldo();
        $model = new Transakcija();
        $sql = "SELECT * FROM {$model->getTable()} WHERE karton_id = :karton_id ORDER BY datum DESC;";
        $params = [':karton_id' => $karton_id];
        $transakcije = $model->paginate($page, $sql, $params, 10);
        $mt = new TipTransakcije();
        $tipovi = $mt->all();

        $this->render($response, 'transakcije.twig', compact('karton', 'transakcije', 'tipovi', 'saldo'));
    }

    public function ajaxGET($request, $response)
    {
        if ($request->isXhr()) { // Da li je ajax
            $txt = $request->getParam('test');
            $res = [
                'tekst' => "ajaxPOST = {$txt}",
                'csrf_name' => $this->csrf->getTokenName(),
                'csrf_value' => $this->csrf->getTokenValue(),
            ];
            return json_encode($res);
        }
    }

    public function ajaxPOST($request, $response)
    {
        if ($request->isXhr()) { // Da li je ajax
            $txt = $request->getParam('test');
            $res = [
                'tekst' => "ajaxPOST = {$txt}",
                'csrf_name' => $this->csrf->getTokenName(),
                'csrf_value' => $this->csrf->getTokenValue(),
            ];
            return json_encode($res);
        }
    }
}
