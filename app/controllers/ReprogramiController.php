<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Zaduzenje;
use App\Models\Racun;
use App\Models\Reprogram;
use App\Models\Uplata;

class ReprogramiController extends Controller
{
	public function getKartonReprogrami($request, $response, $args)
	{
		$karton_id = $args['id'];
		$model_karton = new Karton();
		$karton = $model_karton->find($karton_id);

		$this->render($response, 'transakcije_reprogrami.twig', compact('karton'));
	}

	public function getReprogramDodavanje($request, $response, $args)
	{
		$karton_id = $args['id'];
		$model_karton = new Karton();
		$karton = $model_karton->find($karton_id);

		$model_cene = new Cena();
		$cena_takse = $model_cene->taksa();
		$cena_zakupa = $model_cene->zakup() / 10;

		$this->render($response, 'reprogram_dodavanje.twig', compact('karton', 'cena_takse', 'cena_zakupa'));
	}

	public function postReprogramDodavanje($request, $response)
	{
		$data = $request->getParams();
		$karton_id = (int) $data['karton_id'];
		$korisnik_id = $this->auth->user()->id;
		$reprogram_data = [
			'karton_id' => $karton_id,
			'broj' => $data['broj'],
			'datum' => $data['datum'],
			'iznos' => (float) $data['iznos'],
			'period' => (int) $data['period'],
			'preostalo_rata' => (int) $data['preostalo'],
			'korisnik_id_zaduzio' => $korisnik_id,
			'napomena' => $data['napomena'],
		];
		$zaduzenja_data = isset($data['reprogram-zaduzenje']) ? $data['reprogram-zaduzenje'] : [];
		$racuni_data = isset($data['reprogram-racuni']) ? $data['reprogram-racuni'] : [];

		$validation_rules = [
			'karton_id' => [
				'required' => true,
			],
			'iznos' => [
				'required' => true,
				'min' => 0.01,
			],
			'datum' => [
				'required' => true,
			],
			'period' => [
				'required' => true,
				'min' => 1,
			],
			'preostalo_rata' => [
				'required' => true,
				'min' => 1,
			],
		];

		$this->validator->validate($reprogram_data, $validation_rules);

		// Proveriti da li je uneta makar jedna stavka

		if ($this->validator->hasErrors()) {
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja reprograma.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram.dodavanje', ['id' => $karton_id]));
		} else {
			$model_reprogram = new Reprogram();
			$model_reprogram->insert($reprogram_data);
			$rep_id = (int) $model_reprogram->getLastId();
			if (!empty($zaduzenja_data)) {
				$zad = implode(", ", $zaduzenja_data);
				$sql_zaduzenja = "UPDATE zaduzenja SET reprogram_id = {$rep_id} WHERE id IN ($zad);";
				$model_reprogram->run($sql_zaduzenja);
			}
			if (!empty($racuni_data)) {
				$rac = implode(", ", $racuni_data);
				$sql_racuni = "UPDATE racuni SET reprogram_id = {$rep_id} WHERE id IN ($rac);";
				$model_reprogram->run($sql_racuni);
			}
			$this->flash->addMessage('success', 'Reprogram je uspešno sačuvan.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
		}
	}

	public function getReprogramIzmena($request, $response, $args)
	{
		$reprogram_id = $args['id'];
		$model_reprogram = new Reprogram();
		$reprogram = $model_reprogram->find($reprogram_id);

		$model_cene = new Cena();
		$cena_takse = $model_cene->taksa();
		$cena_zakupa = $model_cene->zakup() / 10;

		$this->render($response, 'reprogram_izmena.twig', compact('reprogram', 'cena_takse', 'cena_zakupa'));
	}

	public function postReprogramIzmena($request, $response)
	{
		$data = $request->getParams();
		$rep_id = (int) $data['reprogram_id'];
		$karton_id = (int) $data['karton_id'];
		$korisnik_id = $this->auth->user()->id;
		$reprogram_data = [
			'broj' => $data['broj'],
			'datum' => $data['datum'],
			'iznos' => (float) $data['iznos'],
			'period' => (int) $data['period'],
			'preostalo_rata' => (int) $data['preostalo_rata'],
			'napomena' => $data['napomena'],
		];
		$zaduzenja_data = isset($data['reprogram-zaduzenje']) ? $data['reprogram-zaduzenje'] : [];
		$racuni_data = isset($data['reprogram-racuni']) ? $data['reprogram-racuni'] : [];

		$validation_rules = [
			'iznos' => [
				'required' => true,
				'min' => 0.01,
			],
			'datum' => [
				'required' => true,
			],
			'period' => [
				'required' => true,
				'min' => 1,
			],
			'preostalo_rata' => [
				'required' => true,
				'min' => 1,
			],
		];

		$this->validator->validate($reprogram_data, $validation_rules);

		// Proveriti da li je uneta makar jedna stavka

		if ($this->validator->hasErrors()) {
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja izmena reprograma.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram.izmena', ['id' => $karton_id]));
		} else {
			$model_reprogram = new Reprogram();
			$model_reprogram->update($reprogram_data, $rep_id);

			$sql_zaduzenja = "UPDATE zaduzenja SET reprogram_id = NULL WHERE reprogram_id = {$rep_id};";
			$sql_racuni = "UPDATE racuni SET reprogram_id = NULL WHERE reprogram_id = {$rep_id};";
			$model_reprogram->run($sql_zaduzenja);
			$model_reprogram->run($sql_racuni);

			if (!empty($zaduzenja_data)) {
				$zad = implode(", ", $zaduzenja_data);
				$sql_zaduzenja = "UPDATE zaduzenja SET reprogram_id = {$rep_id} WHERE id IN ($zad);";
				$model_reprogram->run($sql_zaduzenja);
			}
			if (!empty($racuni_data)) {
				$rac = implode(", ", $racuni_data);
				$sql_racuni = "UPDATE racuni SET reprogram_id = {$rep_id} WHERE id IN ($rac);";
				$model_reprogram->run($sql_racuni);
			}
			$this->flash->addMessage('success', 'Reprogram je uspešno izmenjen.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
		}
	}

	public function postReprogramUplataRate($request, $response)
	{
		$data = $request->getParams();
		$reprogram_id = $data['reprogram_id'];
		$korisnik_id = $this->auth->user()->id;
		$iznos = (float) $data['iznos'];
		$model_reprogram = new Reprogram();
		$reprogram = $model_reprogram->find($reprogram_id);
		$karton_id = $reprogram->karton()->id;

		$uplata_data = [
			'karton_id' => $karton_id,
			'iznos' => $iznos,
			'datum' => $data['datum'],
			'priznanica' => $data['priznanica'],
			'napomena' => $data['napomena'],
			'korisnik_id' => $korisnik_id,
		];
		$validation_rules = [
			'karton_id' => [
				'required' => true,
			],
			'iznos' => [
				'required' => true,
				'min' => 0,
			],
			'datum' => [
				'required' => true,
			],
		];
		$razlika = $iznos - $reprogram->rata() + $reprogram->karton()->saldo;

		if ($razlika <= -0.05) {
			$this->flash->addMessage('danger', 'Iznos uplate ne pokriva ratu reprograma.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogrami', ['id' => $karton_id]));
		}
		$this->validator->validate($data, $validation_rules);
		if ($this->validator->hasErrors()) {
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja uplate i razduživanja.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogrami', ['id' => $karton_id]));
		} else {
			$model_uplata = new Uplata();
			$model_uplata->insert($uplata_data);
			$sql = "UPDATE kartoni SET saldo = {$razlika} WHERE id={$karton_id};";
			$model_uplata->run($sql);
			$preostalo_rata = $reprogram->preostalo_rata - 1;
			$tekst_razduzenja = "{$reprogram->razduzenja}Uplata {$iznos} din od {$uplata_data['datum']}, ";
			// proveriti da li je broj rata 0 i ako jeste razduziti reprogram
			if ($preostalo_rata === 0) {
				$sql_zaduzenja = "UPDATE zaduzenja
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}
                    WHERE reprogram_id = {$reprogram_id};";
				$model_uplata->run($sql_zaduzenja);
				$sql_racuni = "UPDATE racuni
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}
                    WHERE reprogram_id = {$reprogram_id};";
				$model_uplata->run($sql_racuni);
				$sql_reprogram = "UPDATE reprogrami
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}, preostalo_rata = 0,
					razduzenja = '{$tekst_razduzenja}'
                    WHERE id = {$reprogram_id};";
				$model_uplata->run($sql_reprogram);
			} else {
				$sql_reprogram = "UPDATE reprogrami
                    SET preostalo_rata = {$preostalo_rata}, razduzenja = '{$tekst_razduzenja}'
					WHERE id = {$reprogram_id};";
				$model_uplata->run($sql_reprogram);
			}
			$this->flash->addMessage('success', 'Rata reprograma je uplaćena.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogrami', ['id' => $karton_id]));
		}
	}
}
