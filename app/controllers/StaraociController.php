<?php

namespace App\Controllers;

use App\Models\Staraoc;

class StaraociController extends Controller
{
    public function getStaraoci($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $modelStaraoc = new Staraoc;
        $sql = "SELECT * FROM staraoci ORDER BY karton_id, redni_broj;";
        $staraoci = $modelStaraoc->paginate($page, $sql);

        $this->render($response, 'staraoci.twig', compact('staraoci'));
    }

    public function postStaraociPretraga($request, $response)
    {
        $_SESSION['DATA_STARAOCI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('staraoci.pretraga'));
    }

    public function getStaraociPretraga($request, $response)
    {
        $data = $_SESSION['DATA_STARAOCI_PRETRAGA'];
        array_shift($data);
		array_shift($data);
		if(empty( $data['jmbg']) && empty( $data['prezime']) && empty( $data['ime']))
		{
			$this->getStaraoci($request, $response);
		}
        $data['jmbg'] = str_replace('%', '', $data['jmbg']);
        $data['prezime'] = str_replace('%', '', $data['prezime']);
        $data['ime'] = str_replace('%', '', $data['ime']);
        $jmbg = '%' . filter_var($data['jmbg'], FILTER_SANITIZE_STRING) . '%';
        $prezime = '%' . filter_var($data['prezime'], FILTER_SANITIZE_STRING) . '%';
        $ime = '%' . filter_var($data['ime'], FILTER_SANITIZE_STRING) . '%';
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = " WHERE ";
        $params = [];
        if (!empty($data['jmbg'])) {
            $where .= "jmbg LIKE :jmbg";
            $params[':jmbg'] = $jmbg;
        }
        if (!empty($data['prezime'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "prezime LIKE :prezime";
            $params[':prezime'] = $prezime;
        }
        if (!empty($data['ime'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "ime LIKE :ime";
            $params[':ime'] = $ime;
        }
        $where = $where === " WHERE " ? "" : $where;
        $model = new Staraoc();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY karton_id, redni_broj;";
        $staraoci = $model->paginate($page, $sql, $params);

        $this->render($response, 'staraoci.twig', compact('staraoci', 'data'));
    }

    public function getStaraociPregled($request, $response, $args)
    {
        // $id = $args['id'];
        // $modelKarton = new Karton();
        // $karton = $modelKarton->find($id);
        // $saldo = $karton->saldo();
        // $this->render($response, 'karton_pregled.twig', compact('karton', 'saldo'));
    }
}
