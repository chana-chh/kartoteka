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
		// pojedinacno zaduzivanje taksom

		// uracunati avans ako postoji


		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);
		$staraoc_id = $request->getParam('staraoc_id');

		$model = new Staraoc();

		$sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE staraoc_id = :star AND godina = :god AND tip = 1;";
		$broj = $model->fetch($sql, [':god' => $data['godina'], ':star' => $staraoc_id])[0]->broj;

		if ($broj > 0)
		{
			$this->flash->addMessage('danger', 'Već postoji zaduženje za odabranu godinu');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $staraoc_id]));
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
			// 'datum_prispeca' => [
			// 	'required' => true,
			// ],
			// 'datum_prispeca' => [ // test radi i za datum
			// 	'min' => $data['datum_zaduzenja'],
			// ],
			'godina' => [
				'required' => true,
			],
		];

		$this->validator->validate($data, $validation_rules);

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
			return $response->withRedirect($this->router->pathFor('taksa', ['id' => $staraoc_id]));
		}
		else
		{
			$staraoc = $model->find($data['staraoc_id']);
			$bm = $staraoc->karton()->broj_mesta;
			$bs = $staraoc->karton()->brojAktivnihStaraoca();

			$avans = $staraoc->avans;
			$iznos_takse = (float) ($data['iznos_zaduzeno'] * $bm / $bs);

			$model_zaduzenje = new Zaduzenje();

			$podaci = [
				'karton_id' => $staraoc->karton()->id,
				'staraoc_id' => $staraoc->id,
				'tip' => 'taksa',
				'godina' => (int) $data['godina'],
				'iznos_zaduzeno' => $iznos_takse,
				'glavnica' => $iznos_takse,
				'iznos_razduzeno' => 0,
				'razduzeno' => 0,
				'datum_zaduzenja' => $data['datum_zaduzenja'],
				'datum_prispeca' => empty($data['datum_prispeca']) ? null : $data['datum_prispeca'],
				// 'datum_prispeca' => $data['datum_prispeca'],
				'korisnik_id_zaduzio' => $this->auth->user()->id,
				'napomena' => $data['napomena'],
				'avansno' => 0,
				'avans_iznos' => 0,
			];

			if ($avans > 0 && $avans < $iznos_takse)
			{
				$podaci['glavnica'] -= $avans;
				$podaci['iznos_razduzeno'] = $avans;
				$podaci['avans_iznos'] = $avans;
				$avans = 0;
				$podaci['avansno'] = 1;
			}

			if ($avans > $iznos_takse)
			{
				$avans -= $iznos_takse;
				$podaci['glavnica'] = 0;
				$podaci['avansno'] = 1;
				$podaci['avans_iznos'] = $iznos_takse;
				$podaci['iznos_razduzeno'] = $iznos_takse;
				$podaci['razduzeno'] = 1;
				$podaci['datum_razduzenja'] = $data['datum_zaduzenja'];
				$podaci['korisnik_id_razduzio'] = $this->auth->user()->id;
			}

			$model_zaduzenje->insert($podaci);
			$id = $model_zaduzenje->getLastId();

			$sql_avans = "UPDATE staraoci SET avans = {$avans} WHERE id = {$staraoc->id};";
			$staraoc->run($sql_avans);

			$zazduzenje = $model_zaduzenje->find($id);
			$this->log($this::DODAVANJE, $zazduzenje, ['tip', 'godina'], $zazduzenje);
			$this->flash->addMessage('success', 'Staraoc je uspešno zadužen odgovarajućom taksom.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $staraoc_id]));
		}
	}
}
