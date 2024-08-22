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

	// pojedinacno zaduzivanje racunom
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
			// TODO dodati unique za broj
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

		// posto su sve provere prosle vrsi se zaduzivanje staraoca racunom
		$iznos_racuna = (float) $data['iznos'];
		$staraoc = (new Staraoc())->find((int) $data['staraoc_id']);

		if ($staraoc->aktivan === 0)
		{
			$this->flash->addMessage('danger', 'Staraoc nije aktivan.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $data['staraoc_id']]));
		}

		$model_racun = new Racun();
		$korisnik_id = $this->auth->user()->id;

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
			'korisnik_id_zaduzio' => $korisnik_id,
			'korisnik_id_razduzio' => null,
			'napomena' => $data['napomena'],
			'datum_prispeca' => null,
			// XXX datum prispeca samo kad se krene sa zateznom kamatom
			// 'datum_prispeca' => empty($data['rok']) ? null : $data['rok'],
			'avansno' => 0,
			'avans_iznos' => 0,
		];

		$model_racun->insert($podaci);
		$id = $model_racun->getLastId();

		// razduzivanje/umanjenje zaduzenja avansom

		$avans = $staraoc->avans();

		// proveri se da li staraoc ima avans $staraoc->avans() > 0
		if ($avans > 0) // ako ima avans poksati razduzivanje novounetog zaduzenja postojecim avansom
		{
			$uplate_sa_avansom = $staraoc->uplateSaAvansom();

			$data_za_razduzenje = [
				'datum_prispeca' => null,
				'poslednji_datum_prispeca' => null,
				'avansno' => 1,
			];

			$data_r_u = [
				'zaduzenje_id' => $id,
				'staraoc_id' => $staraoc->id,
				'avansno' => 1,
			];

			$model_r_u = new RacunUplata();

			foreach ($uplate_sa_avansom as $ua)
			{
				$racun = $model_racun->find($id);

				// za svaku uplatu se proveri da li je dovoljna za razduzivanje racuna
				if ($ua->avans >= $racun->glavnica) // ili glavnica
				{
					// ako je dovoljno da se razduzi ceo racun
					// razduzivanje celog racuna
					$data_za_razduzenje['razduzeno'] = 1;
					$data_za_razduzenje['korisnik_id_razduzio'] = $korisnik_id;
					$data_za_razduzenje['datum_razduzenja'] = $data['datum_zaduzenja'];
					$data_za_razduzenje['uplata_id'] = $ua->id;
					$data_za_razduzenje['iznos_razduzeno'] = $racun->iznos_zaduzeno;
					$data_za_razduzenje['poslednja_glavnica'] = $racun->glavnica;
					$data_za_razduzenje['glavnica'] = 0;
					$racun->update($data_za_razduzenje, $racun->id);
					// unos racun_uplata
					$data_r_u['uplata_id'] = $ua->id;
					$data_r_u['uplata_datum'] = $ua->datum;
					$data_r_u['iznos'] = $racun->glavnica;
					$data_r_u['iznos_prethodni'] = $racun->glavnica;
					$model_r_u->insert($data_r_u);
					// osvezava se uplata (skida se iznos koji je otiso na razduzivanje takse sa avansa uplate)
					$ua->update(['avans' => $ua->avans - $racun->glavnica], $ua->id);
					break; // prekida se foreach jer je razduzen ceo racun
				}
				else
				{
					// ako nije dovoljno da se razduzi ceo racun
					// delimicno razduzivanje racuna
					$data_za_razduzenje['razduzeno'] = 0;
					$data_za_razduzenje['korisnik_id_razduzio'] = null;
					$data_za_razduzenje['datum_razduzenja'] = null;
					$data_za_razduzenje['uplata_id'] = $ua->id;
					$data_za_razduzenje['iznos_razduzeno'] = $racun->iznos_razduzeno + $ua->avans;
					$data_za_razduzenje['poslednja_glavnica'] = $racun->glavnica;
					$data_za_razduzenje['glavnica'] = $racun->glavnica - $ua->avans;
					$racun->update($data_za_razduzenje, $racun->id);
					// unos racun_uplata
					$data_r_u['uplata_id'] = $ua->id;
					$data_r_u['uplata_datum'] = $ua->datum;
					$data_r_u['iznos'] = $ua->avans;
					$data_r_u['iznos_prethodni'] = $racun->glavnica;
					$model_r_u->insert($data_r_u);
					// osvezava se uplata (skida se iznos koji je otiso na razduzivanje racuna sa avansa uplate)
					$ua->update(['avans' => 0], $ua->id);
				}
			}
		}

		$staraoc->update(['avans' => $staraoc->avans()], $staraoc->id);

		$racun = $model_racun->find($id);
		$this->log($this::DODAVANJE, $racun, ['broj', 'datum'], $racun);
		$this->flash->addMessage('success', 'Karton je uspešno zadužen računom.');
		return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $data['staraoc_id']]));
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
