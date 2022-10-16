<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Staraoc;
use App\Models\Zaduzenje;
use DateTime;

class TaksaController extends Controller
{

	public function getTaksa($request, $response, $args)
	{
		$staraoc_id = (int) $args['id'];
		$staraoc = (new Staraoc())->find($staraoc_id);
		$cene = new Cena();

		$this->render($response, 'taksa.twig', compact('cene', 'staraoc'));
	}

	public function postTaksa($request, $response)
	{
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);
		$id = $request->getParam('staraoc_id');

		$model = new Staraoc();

		$sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE staraoc_id = :star AND godina = :god AND tip = 1;";
		$broj = $model->fetch($sql, [':god' => $data['godina'], ':star' => $id])[0]->broj;
		
		if ($broj > 0)
		{
			$this->flash->addMessage('danger', 'Već postoji zaduženje za odabranu godinu');
			return $response->withRedirect($this->router->pathFor('taksa', ['id' => $id]));
		}

		$validation_rules = [
			'iznos_zaduzeno' => [
				'required' => true,
			],
			'iznos_zaduzeno' => [
				'min' => 0.01,
			],
			'datum_zaduzenja' => [
				'required' => true,
			],
			'datum_prispeca' => [
				'required' => true,
			],
			'datum_prispeca' => [ // test radi i za datum
				'min' => $data['datum_zaduzenja'],
			],
			'godina' => [
				'required' => true,
			],
		];

		$this->validator->validate($data, $validation_rules);

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
			return $response->withRedirect($this->router->pathFor('taksa', ['id' => $id]));
		}
		else
		{
			$staraoc = $model->find($data['staraoc_id']);
			$bm = $staraoc->karton()->broj_mesta;
			$bs = $staraoc->karton()->brojAktivnihStaraoca();

			$model_zaduzenje = new Zaduzenje();
			$model_zaduzenje->insert([
				'karton_id' => $staraoc->karton()->id,
				'staraoc_id' => $staraoc->id,
				'tip' => 'taksa',
				'godina' => (int) $data['godina'],
				'iznos_zaduzeno' => (float) ($data['iznos_zaduzeno'] * $bm / $bs),
				'glavnica' => (float) ($data['iznos_zaduzeno'] * $bm / $bs),
				'iznos_razduzeno' => 0,
				'razduzeno' => 0,
				'datum_zaduzenja' => $data['datum_zaduzenja'],
				'datum_prispeca' => $data['datum_prispeca'],
				'korisnik_id_zaduzio' => $this->auth->user()->id,
				'napomena' => $data['napomena'],
			]);

			$id = $model_zaduzenje->getLastId();
			$zazduzenje = $model_zaduzenje->find($id);
			$this->log($this::DODAVANJE, $zazduzenje, ['tip', 'godina'], $zazduzenje);
			$this->flash->addMessage('success', 'Staraoc je uspešno zadužen odgovarajućom taksom.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $id]));
		}
	}
}
