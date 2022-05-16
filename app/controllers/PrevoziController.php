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
		$sql = "SELECT * FROM prevozi ORDER BY datum DESC, vreme DESC;";
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

		if (empty($data['prezime']) && empty($data['ime']) && empty($data['datum_1']) && empty($data['pok_ime']) && empty($data['pok_prezime']))
		{
			return $this->getPrevozi($request, $response);
		}

		$data['prezime'] = str_replace('%', '', $data['prezime']);
		$data['ime'] = str_replace('%', '', $data['ime']);
		$data['pok_prezime'] = str_replace('%', '', $data['pok_prezime']);
		$data['pok_ime'] = str_replace('%', '', $data['pok_ime']);
		$prezime = '%' . filter_var($data['prezime'], FILTER_SANITIZE_STRING) . '%';
		$ime = '%' . filter_var($data['ime'], FILTER_SANITIZE_STRING) . '%';
		$pok_prezime = '%' . filter_var($data['pok_prezime'], FILTER_SANITIZE_STRING) . '%';
		$pok_ime = '%' . filter_var($data['pok_ime'], FILTER_SANITIZE_STRING) . '%';
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

		if (!empty($data['pok_prezime']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "pok_prezime LIKE :pok_prezime";
			$params[':pok_prezime'] = $pok_prezime;
		}

		if (!empty($data['pok_ime']))
		{
			if ($where !== " WHERE ")
			{
				$where .= " AND ";
			}
			$where .= "pok_ime LIKE :pok_ime";
			$params[':pok_ime'] = $pok_ime;
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
		$sql = "SELECT * FROM prevozi{$where} ORDER BY datum DESC, vreme DESC;";
		$prevozi = $model->paginate($page, $sql, $params);
		$this->render($response, 'prevozi.twig', compact('prevozi', 'data'));
	}

	public function getPrevoziDodavanje($request, $response)
	{
		$this->render($response, 'prevoz_dodavanje.twig');
	}

	public function postPrevoziDodavanje($request, $response)
	{
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);
		$data['korisnik_id'] = $this->auth->user()->id;

		$validation_rules = [
			'datum' => [
				'required' => true,
			],
			'vreme' => [
				'required' => true,
			],
			'prezime' => [
				'required' => true,
			],
			'ime' => [
				'required' => true,
			],
			'telefon' => [
				'required' => true,
			],
			'pok_prezime' => [
				'required' => true,
			],
			'pok_ime' => [
				'required' => true,
			],
			'od_mesto' => [
				'required' => true,
			],
			'od_ulica' => [
				'required' => true,
			],
			'od_broj' => [
				'required' => true,
			],
			'do_mesto' => [
				'required' => true,
			],
			'do_ulica' => [
				'required' => true,
			],
			'do_broj' => [
				'required' => true,
			],
		];

		$this->validator->validate($data, $validation_rules);

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja prevoza.');
			return $response->withRedirect($this->router->pathFor('prevozi.dodavanje.get'));
		}
		else
		{
			$model = new Prevoz();
			$model->insert($data);
			$id = $model->getLastId();
			$prevoz = $model->find($id);
			$this->log($this::DODAVANJE, $prevoz, ['prezime', 'ime'], $prevoz);
			$this->flash->addMessage('success', 'Novi prevoz je uspešno upisan.');
			return $response->withRedirect($this->router->pathFor('prevozi'));
		}
	}

	public function getPrevoziIzmena($request, $response, $args)
	{
		$id = (int) $args['id'];
		$prevoz = (new Prevoz())->find($id);
		$this->render($response, 'prevoz_izmena.twig', compact('prevoz'));
	}

	public function postPrevoziIzmena($request, $response)
	{
		$id = (int) $request->getParam('prevoz_id');
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);
		unset($data['prevoz_id']);
		$data['korisnik_id'] = $this->auth->user()->id;

		$validation_rules = [
			'datum' => [
				'required' => true,
			],
			'vreme' => [
				'required' => true,
			],
			'prezime' => [
				'required' => true,
			],
			'ime' => [
				'required' => true,
			],
			'telefon' => [
				'required' => true,
			],
			'pok_prezime' => [
				'required' => true,
			],
			'pok_ime' => [
				'required' => true,
			],
			'od_mesto' => [
				'required' => true,
			],
			'od_ulica' => [
				'required' => true,
			],
			'od_broj' => [
				'required' => true,
			],
			'do_mesto' => [
				'required' => true,
			],
			'do_ulica' => [
				'required' => true,
			],
			'do_broj' => [
				'required' => true,
			],
		];

		$this->validator->validate($data, $validation_rules);

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene podataka o prevozu.');
			return $response->withRedirect($this->router->pathFor('prevozi.izmena.get'));
		}
		else
		{
			$model = new Prevoz();
			$prevoz = $model->find($id);
			$model->update($data, $id);
			$novi = $model->find($id);
			$this->log($this::IZMENA, $novi, ['prezime', 'ime'], $prevoz);
			$this->flash->addMessage('success', 'Prevoz je uspešno IZMENJEN.');
			return $response->withRedirect($this->router->pathFor('prevozi'));
		}
	}

	public function postPrevoziBrisanje($request, $response)
	{
		$id = (int) $request->getParam('modal_prevoz_id');

		if ($this->auth->user()->nivo !== 0)
		{
			$this->flash->addMessage('danger', "Samo administrator može da obriše prevoz.");
			return $response->withRedirect($this->router->pathFor('prevozi'));
		}

		$model = new Prevoz();
		$prevoz = $model->find($id);
		$success = $model->deleteOne($id);

		if ($success)
		{
			$this->log($this::BRISANJE, $prevoz, ['datum', 'vreme', 'prezime', 'ime'], $prevoz);
			$this->flash->addMessage('success', "Prevoz je uspešno obrisan.");
			return $response->withRedirect($this->router->pathFor('prevozi'));
		}
		else
		{
			$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja prevoza.");
			return $response->withRedirect($this->router->pathFor('prevozi'));
		}
	}
}
