<?php

namespace App\Controllers;

use App\Models\Raspored;
use App\Models\Korisnik;
use App\Models\Log;

class LogController extends Controller
{
	public function getLog($request, $response)
	{

		$query = [];
		parse_str($request->getUri()->getQuery(), $query);
		$page = isset($query['page']) ? (int)$query['page'] : 1;

		$modelLog = new Log();
		$sql = "SELECT * FROM logovi ORDER BY datum DESC;";
		$logovi = $modelLog->paginate($page, $sql);

		$modelKorisnika = new Korisnik();
		$korisnici = $modelKorisnika->all();

		$this->render($response, 'log.twig', compact('logovi', 'korisnici'));
	}

	public function postLogoviPretraga($request, $response)
	{
		$_SESSION['DATA_LOGOVI_PRETRAGA'] = $request->getParams();
		return $response->withRedirect($this->router->pathFor('logovi.pretraga'));
	}

	public function getLogoviPretraga($request, $response)
	{
		$data = $_SESSION['DATA_LOGOVI_PRETRAGA'];
		array_shift($data);
		array_shift($data);
		if (
			empty($data['opis']) &&
			empty($data['izmene']) &&
			empty($data['tip']) &&
			empty($data['datum_1']) &&
			empty($data['datum_2']) &&
			empty($data['korisnik_id'])
		)
		{
			$this->getLog($request, $response);
		}

		$data['opis'] = str_replace('%', '', $data['opis']);
		$data['izmene'] = str_replace('%', '', $data['izmene']);
		$opis = '%' . filter_var($data['opis'], FILTER_SANITIZE_STRING) . '%';
		$izmene = '%' . filter_var($data['izmene'], FILTER_SANITIZE_STRING) . '%';
		$query = [];
		parse_str($request->getUri()->getQuery(), $query);
		$page = isset($query['page']) ? (int)$query['page'] : 1;

		$where = " WHERE ";
		$params = [];

		if (!empty($data['opis']))
		{
			$where .= "opis LIKE :opis";
			$params[':opis'] = $opis;
		}

		if (!empty($data['izmene']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "izmene LIKE :izmene";
			$params[':izmene'] = $izmene;
		}

		if (!empty($data['tip']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "tip LIKE :tip";
			$params[':tip'] = $data['tip'];
		}

		if (!empty($data['korisnik_id']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "korisnik_id = :korisnik_id";
			$params[':korisnik_id'] = $data['korisnik_id'];
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
		$modelKorisnika = new Korisnik();
		$korisnici = $modelKorisnika->all();
		$model = new Log();
		$sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY datum DESC;";
		$logovi = $model->paginate($page, $sql, $params);

		$this->render($response, 'log.twig', compact('logovi', 'data'));
	}
}
