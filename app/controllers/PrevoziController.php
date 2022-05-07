<?php

namespace App\Controllers;

use App\Models\Prevoz;

class PrevoziController extends Controller
{
	public function getPrevozi($request, $response)
	{
		$query = [];
		parse_str($request->getUri()->getQuery(), $query);
		$page = isset($query['page']) ? (int) $query['page'] : 1;

		$model = new Prevoz();
		$sql = "SELECT * FROM prevozi ORDER BY datum, vreme;";
		$prevozi = $model->paginate($page, $sql);

		$this->render($response, 'prevozi.twig', compact('prevozi'));
	}

	public function postPrevoziPretraga($request, $response)
	{
		$_SESSION['DATA_PREVOZI_PRETRAGA'] = $request->getParams();
		return $response->withRedirect($this->router->pathFor('prevozi.pretraga'));
	}

	public function getPrevoziPretraga($request, $response)
	{
		$data = $_SESSION['DATA_PREVOZI_PRETRAGA'];
		array_shift($data);
		array_shift($data);

		if (empty($data['prezime']) && empty($data['ime']) && empty($data['datum_1']))
		{
			return $this->getPrevozi($request, $response);
		}

		$data['prezime'] = str_replace('%', '', $data['prezime']);
		$data['ime'] = str_replace('%', '', $data['ime']);
		$prezime = '%' . filter_var($data['prezime'], FILTER_SANITIZE_STRING) . '%';
		$ime = '%' . filter_var($data['ime'], FILTER_SANITIZE_STRING) . '%';
		$query = [];
		parse_str($request->getUri()->getQuery(), $query);
		$page = isset($query['page']) ? (int)$query['page'] : 1;

		$where = " WHERE ";
		$params = [];

		if (!empty($data['prezime']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "prezime LIKE :prezime";
			$params[':prezime'] = $prezime;
		}

		if (!empty($data['ime']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "ime LIKE :ime";
			$params[':ime'] = $ime;
		}

		if (!empty($data['datum_1']) && empty($data['datum_2']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "DATE(datum) = :datum_1";
			$params[':datum_1'] = $data['datum_1'];
		}

		if (!empty($data['datum_1']) && !empty($data['datum_2']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "DATE(datum) >= :datum_1 AND DATE(datum) <= :datum_2 ";
			$params[':datum_1'] = $data['datum_1'];
			$params[':datum_2'] = $data['datum_2'];
		}

		$where = $where === " WHERE " ? "" : $where;
		$model = new Prevoz();
		$sql = "SELECT * FROM prevozi{$where} ORDER BY datum, vreme;";
		$prevozi = $model->paginate($page, $sql, $params);

		$this->render($response, 'prevozi.twig', compact('prevozi', 'data'));
	}
}
