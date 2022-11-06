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
		// pojedinacno zaduzivanje racunima

		// uracunati avans ako postoji


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
			$iznos_racuna = (float) $data['iznos'];
			$staraoc = (new Staraoc())->find($data['staraoc_id']);
			$avans = $staraoc->avans;

			$podaci = [
				'karton_id' => $data['karton_id'],
				'staraoc_id' => $data['staraoc_id'],
				'broj' => $data['broj'],
				'datum' => $data['datum'],
				'iznos' => $iznos_racuna,
				'glavnica' => $iznos_racuna,
				'iznos_razduzeno' => 0,
				'razduzeno' => 0,
				'datum_razduzenja' => null,
				'korisnik_id_zaduzio' => $this->auth->user()->id,
				'korisnik_id_razduzio' => null,
				'napomena' => $data['napomena'],
				'datum_prispeca' => $data['rok'],
				'avansno' => 0,
				'avans_iznos' => 0,
			];

			if ($avans > 0 && $avans < $iznos_racuna)
			{
				$podaci['glavnica'] -= $avans;
				$podaci['iznos_razduzeno'] = $avans;
				$podaci['avans_iznos'] = $avans;
				$avans = 0;
				$podaci['avansno'] = 1;
			}

			if ($avans > $iznos_racuna)
			{
				$avans -= $iznos_racuna;
				$podaci['glavnica'] = 0;
				$podaci['avansno'] = 1;
				$podaci['avans_iznos'] = $iznos_racuna;
				$podaci['iznos_razduzeno'] = $iznos_racuna;
				$podaci['razduzeno'] = 1;
				$podaci['datum_razduzenja'] = $data['datum_zaduzenja'];
				$podaci['korisnik_id_razduzio'] = $this->auth->user()->id;
			}

			$model = new Racun();
			$model->insert($podaci);
			$id = $model->getLastId();

			$sql_avans = "UPDATE staraoci SET avans = {$avans} WHERE id = {$staraoc->id};";
			$staraoc->run($sql_avans);

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
