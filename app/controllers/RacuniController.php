<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Staraoc;
use App\Models\Racun;

class RacuniController extends Controller
{
	public function getRacun($request, $response, $args)
	{
		$staraoc_id = $args['id'];
		$staraoc = (new Staraoc())->find($staraoc_id);

		$this->render($response, 'racun.twig', compact('staraoc'));
	}

	public function postRacun($request, $response)
	{
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);
		$data['rok'] = empty($data['rok']) ? null : $data['rok'];
		$validation_rules = [
			'karton_id' => [
				'required' => true,
			],
			'staraoc_id' => [
				'required' => true,
			],
			'broj' => [
				'required' => true,
			],
			'datum' => [
				'required' => true,
			],
			'iznos' => [
				'required' => true,
				'min' => 0.01,
			]
		];

		$this->validator->validate($data, $validation_rules);

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduženja računa.');
			return $response->withRedirect($this->router->pathFor('racun', ['id' => $data['staraoc_id']]));
		}
		else
		{
			$data['razduzeno'] = 0;
			$data['korisnik_id_zaduzio'] = $this->auth->user()->id;
			$model = new Racun();
			$model->insert($data);
			$id = $model->getLastId();
			$racun = $model->find($id);
			$this->log($this::DODAVANJE, $racun, ['broj', 'datum'], $racun);
			$this->flash->addMessage('success', 'Karton je uspešno zadužen računom.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $data['staraoc_id']]));
		}
	}

	public function postRacunBrisanje($request, $response)
	{
		$id = (int) $request->getParam('modal_racun_id');
		$racun = (new Racun())->find($id);

		// ne moze da se brise ako je
		// razduzeno = 1
		// reprogram_id != null
		if ($racun->razduzeno === 1 || $racun->reprogram_id != null)
		{
			$this->flash->addMessage('danger', "Postoje transakcije vezane za ovaj račun.");
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $racun->staraoc()->id]));
		}

		$success = $racun->deleteOne($id);

		if ($success)
		{
			$this->log($this::BRISANJE, $racun, ['broj', 'datum'], $racun);
			$this->flash->addMessage('success', "Račun je uspešno obrisan.");
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $racun->staraoc()->id]));
		}
		else
		{
			$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja računa.");
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $racun->staraoc()->id]));
		}
	}
}
