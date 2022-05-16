<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Staraoc;
use App\Models\Zaduzenje;
use App\Models\Racun;
use App\Models\Reprogram;
use App\Models\Uplata;
use Slim\Http\Request;

class ReprogramiController extends Controller
{
	public function getKartonReprogrami($request, $response, $args)
	{
		$id = (int) $args['id'];
		$staraoc = (new Staraoc())->find($id);

		$this->render($response, 'transakcije_reprogrami.twig', compact('staraoc'));
	}

	public function getReprogram($request, $response, $args)
	{
		$id = (int) $args['id'];
		$reprogram = (new Reprogram())->find($id);

		$this->render($response, 'reprogram.twig', compact('reprogram'));
	}

	public function getReprogramDodavanje($request, $response, $args)
	{
		$id = (int) $args['id'];
		$staraoc = (new Staraoc)->find($id);

		$cene = new Cena();

		$this->render($response, 'reprogram_dodavanje.twig', compact('staraoc', 'cene'));
	}

	public function postReprogramDodavanje($request, $response)
	{
		$data = $request->getParams();

		$id = (int) $data['staraoc_id'];
		$staraoc = (new Staraoc())->find($id);

		$korisnik_id = $this->auth->user()->id;

		$zaduzenja_data = isset($data['razduzeno-zaduzenje']) ? $data['razduzeno-zaduzenje'] : [];
		$racuni_data = isset($data['razduzeno-racuni']) ? $data['razduzeno-racuni'] : [];

		unset($data['razduzeno-zaduzenje']);
		unset($data['razduzeno-racuni']);

		$validation_rules = [
			'staraoc_id' => [
				'required' => true,
			],
			'broj' => [
				'required' => true,
			],
			'iznos' => [
				'required' => true,
				'min' => 0.01,
			],
			'iznos_rate' => [
				'required' => true,
				'min' => 0.01,
			],
			'datum' => [
				'required' => true,
			],
			'datum_prve_rate' => [
				'required' => true,
			],
			'period' => [
				'required' => true,
				'min' => 1,
			],
			'preostalo' => [
				'required' => true,
				'min' => 1,
			],
		];

		if ($zaduzenja_data === [] && $racuni_data === [])
		{
			$this->flash->addMessage('danger', 'Nije odabrana nijedna stavka za prebacivanje u reprogam.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram.dodavanje', ['id' => $id]));
		}

		$this->validator->validate($data, $validation_rules);


		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja reprograma.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram.dodavanje', ['id' => $id]));
		}
		else
		{
			$iznos_rate = (float) $data['iznos'] / (int) $data['preostalo_rata'];

			$reprogram_data = [
				'staraoc_id' => $staraoc->id,
				'karton_id' => $staraoc->karton()->id,
				'broj' => $data['broj'],
				'datum' => $data['datum'],
				'datum_prve_rate' => $data['datum_prve_rate'],
				'iznos' => (float) $data['iznos'],
				'iznos_rate' => round($iznos_rate, 6),
				'period' => (int) $data['period'],
				'preostalo_rata' => (int) $data['preostalo_rata'],
				'korisnik_id_zaduzio' => $korisnik_id,
				'napomena' => $data['napomena'],
			];

			$model = new Reprogram();
			$model->insert($reprogram_data);
			$rep_id = (int) $model->getLastId();

			if (!empty($zaduzenja_data))
			{
				$zad = implode(", ", $zaduzenja_data);
				$sql_zaduzenja = "UPDATE zaduzenja SET reprogram_id = {$rep_id} WHERE id IN ($zad);";
				$model->run($sql_zaduzenja);
			}

			if (!empty($racuni_data))
			{
				$rac = implode(", ", $racuni_data);
				$sql_racuni = "UPDATE racuni SET reprogram_id = {$rep_id} WHERE id IN ($rac);";
				$model->run($sql_racuni);
			}
			$repzalog = $model->find($id);
			$this->log($this::DODAVANJE, $repzalog, ['broj', 'datum', 'period'], $repzalog);
			$this->flash->addMessage('success', 'Reprogram je uspešno sačuvan.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $id]));
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

		$id = (int) $data['reprogram_id'];
		$model = new Reprogram();
		$stari = $model->find($id);
		$korisnik_id = $this->auth->user()->id;
		$reprogram_data = [
			'broj' => $data['broj'],
			'datum' => $data['datum'],
			'datum_prve_rate' => $data['datum_prve_rate'],
			'napomena' => $data['napomena'],
		];

		$validation_rules = [
			'broj' => [
				'required' => true,
			],
			'datum' => [
				'required' => true,
			],
			'datum_prve_rate' => [
				'required' => true,
			],
		];

		$this->validator->validate($reprogram_data, $validation_rules);

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja izmena reprograma.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram', ['id' => $id]));
		}
		else
		{
			(new Reprogram())->update($reprogram_data, $id);
			$novi = $model->find($id);
			$this->log($this::IZMENA, $novi, ['broj', 'datum', 'period'], $stari);
			$this->flash->addMessage('success', 'Reprogram je uspešno izmenjen.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram', ['id' => $id]));
		}
	}

	public function postReprogramUplataRate($request, $response)
	{
		$data = $request->getParams();

		$id = (int) $data['reprogram_id'];

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

		$this->validator->validate($data, $validation_rules);

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja uplate i razduživanja.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram', ['id' => $id]));
		}
		else
		{
			$reprogram = (new Reprogram())->find($id);
			$broj_rata = (int) $data['broj_rata'];
			$korisnik_id = $this->auth->user()->id;
			$iznos = round($reprogram->iznos_rate * $broj_rata, 2);

			$uplata_data = [
				'karton_id' => $reprogram->karton()->id,
				'staraoc_id' => $reprogram->staraoc()->id,
				'iznos' => $iznos,
				'datum' => $data['datum'],
				'priznanica' => $data['priznanica'],
				'napomena' => $data['napomena'],
				'korisnik_id' => $korisnik_id,
				'reprogram_id' => $reprogram->id,
				'broj_rata' => $broj_rata,
			];

			$model_uplata = new Uplata();
			$model_uplata->insert($uplata_data);
			$uplata_id = $model_uplata->getLastId();
			$uplata = $model_uplata->find($uplata_id);
			$preostalo_rata = $reprogram->preostalo_rata - $broj_rata;

			// proveriti da li je broj rata 0 i ako jeste razduziti reprogram
			if ($preostalo_rata <= 0)
			{
				$sql_zaduzenja = "UPDATE zaduzenja
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id},
					iznos_razduzeno = iznos_zaduzeno
                    WHERE reprogram_id = {$id};";
				$model_uplata->run($sql_zaduzenja);

				$sql_racuni = "UPDATE racuni
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}
                    WHERE reprogram_id = {$id};";
				$model_uplata->run($sql_racuni);

				$sql_reprogram = "UPDATE reprogrami
                    SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id}, preostalo_rata = 0
                    WHERE id = {$id};";
				$model_uplata->run($sql_reprogram);
			}
			else
			{
				$sql_reprogram = "UPDATE reprogrami
                    SET preostalo_rata = {$preostalo_rata}
					WHERE id = {$id};";
				$model_uplata->run($sql_reprogram);
			}

			$this->log($this::DODAVANJE, $uplata, ['karton_id', 'iznos', 'datum'], $uplata);
			$this->flash->addMessage('success', 'Rata reprograma je uplaćena.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram', ['id' => $id]));
		}
	}

	public function postReprogramBrisanje($request, $response)
	{
		$id = (int) $request->getParam('modal_reprogram_id');
		$reprogram = (new Reprogram())->find($id);

		if (!empty($reprogram->uplate()))
		{
			$this->flash->addMessage('danger', 'Postoje proknjižene uplate za ovaj reprogram.');
			return $response->withRedirect($this->router->pathFor('transakcije.reprogram', ['id' => $id]));
		}

		$sql = "UPDATE zaduzenja SET reprogram_id = NULL WHERE reprogram_id = {$id};";
		$reprogram->run($sql);

		$sql = "UPDATE racuni SET reprogram_id = NULL WHERE reprogram_id = {$id};";
		$reprogram->run($sql);

		$staraoc_id = $reprogram->staraoc()->id;

		$success = $reprogram->deleteOne($id);

		if ($success)
		{
			$this->log($this::BRISANJE, $reprogram, ['broj', 'datum', 'period'], $reprogram);
			$this->flash->addMessage('success', "Reprogram je uspešno obrisan.");
			return $response->withRedirect($this->router->pathFor('transakcije.reprogrami', ['id' => $staraoc_id]));
		}
		else
		{
			$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja reprograma.");
			return $response->withRedirect($this->router->pathFor('transakcije.reprogrami', ['id' => $staraoc_id]));
		}
	}
}
