<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Staraoc;
use App\Models\Zaduzenje;
use App\Models\ZaduzenjeUplata;
use DateTime;

class TaksaController extends Controller
{
	// forma za zaduzivanje staraoca taksom
	public function getTaksa($request, $response, $args)
	{
		$staraoc_id = (int) $args['id'];
		$staraoc = (new Staraoc())->find($staraoc_id);
		$cene = new Cena();

		$this->render($response, 'taksa.twig', compact('cene', 'staraoc'));
	}

	// pojedinacno zaduzivanje staraoca taksom
	public function postTaksa($request, $response)
	{
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);
		$staraoc_id = $data['staraoc_id'];

		$model = new Staraoc();

		// trazi se zaduzenje (taksa) sa prosledjenim parametrima
		$sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE staraoc_id = :star AND godina = :god AND tip = 1;";
		$broj = $model->fetch($sql, [':god' => $data['godina'], ':star' => $staraoc_id])[0]->broj;

		// ako je pronadjeno zaduzenje znaci da zaduzenje za godinu vec postoji i staraoc se ne zaduzuje
		if ($broj > 0)
		{
			$this->flash->addMessage('danger', 'Već postoji zaduženje za odabranu godinu');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $staraoc_id]));
		}

		// pravila za proveru podataka
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
			// XXX ovo je za datum od kada pocinje da se racuna zatezna kamata
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

		// ako postoji greska u unetim podacima
		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
			return $response->withRedirect($this->router->pathFor('taksa', ['id' => $staraoc_id]));
		}

		// ako su podaci ispravni vrsi se preuzimanje staraoca
		$staraoc = $model->find($data['staraoc_id']);

		// ako staraoc nije aktivan nije moguce da se zaduzi taksom
		if ($staraoc->aktivan === 0)
		{
			$this->flash->addMessage('danger', 'Staraoc nije aktivan.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $data['staraoc_id']]));
		}

		// posto su sve provere prosle vrsi se zaduzivanje staraoca taksom
		$bm = $staraoc->karton()->broj_mesta;
		$bs = $staraoc->karton()->brojAktivnihStaraoca();

		// iznos ukupne takse je cena * broj grobnih mesta / broj aktivnih staraoca na kartonu
		$iznos_takse = (float) ($data['iznos_zaduzeno'] * $bm / $bs);

		$model_zaduzenje = new Zaduzenje();
		$korisnik_id = $this->auth->user()->id;

		$podaci = [
			'karton_id' => $staraoc->karton()->id,
			'staraoc_id' => $staraoc->id,
			'tip' => 'taksa',
			'godina' => (int) $data['godina'],
			'iznos_zaduzeno' => $iznos_takse,
			// XXX glavnica samo kad se krene sa zateznom kamatom
			// koristi se na vise mesta umesto iznosa za razduzenje pa zbog oga ostaje
			'glavnica' => $iznos_takse,
			'iznos_razduzeno' => 0,
			'razduzeno' => 0,
			'datum_zaduzenja' => $data['datum_zaduzenja'],
			// XXX datum prispeca samo kad se krene sa zateznom kamatom
			// 'datum_prispeca' => empty($data['datum_prispeca']) ? null : $data['datum_prispeca'],
			'datum_prispeca' => null,
			'korisnik_id_zaduzio' => $korisnik_id,
			'napomena' => $data['napomena'],
			'avansno' => 0,
			'avans_iznos' => 0,
		];

		$model_zaduzenje->insert($podaci);
		$id = $model_zaduzenje->getLastId();

		// razduzivanje/umanjenje zaduzenja avansom

		// FIXME avans da se vadi iz uplata staraoca
		// $avans = $staraoc->avans; trebalo bi da bude isto ($staraoc->avans i $staraoc->avans())
		$avans = $staraoc->avans();

		// proveri se da li staraoc ima avans $staraoc->avans() > 0 ?
		if ($avans > 0)
		{
			$uplate_sa_avansom = $staraoc->uplateSaAvansom();

			$data_za_razduzenje = [
				'datum_prispeca' => null,
				'poslednji_datum_prispeca' => null,
				'avansno' => 1,
			];

			$data_z_u = [
				'zaduzenje_id' => $id,
				'staraoc_id' => $staraoc->id,
				'avansno' => 1,
			];

			$model_z_u = new ZaduzenjeUplata;

			foreach ($uplate_sa_avansom as $ua)
			{
				$taksa = $model_zaduzenje->find($id);

				// za svaku uplatu se proveri da li je dovoljna za razduzivanje takse
				if ($ua->avans >= $taksa->glavnica) // ili glavnica
				{
					// ako je dovoljno da se razduzi celo zaduzenje-taksa
					// razduzivanje celog zaduzenja
					$data_za_razduzenje['razduzeno'] = 1;
					$data_za_razduzenje['korisnik_id_razduzio'] = $korisnik_id;
					$data_za_razduzenje['datum_razduzenja'] = $data['datum_zaduzenja'];
					$data_za_razduzenje['uplata_id'] = $ua->id;
					$data_za_razduzenje['iznos_razduzeno'] = $taksa->iznos_zaduzeno;
					$data_za_razduzenje['poslednja_glavnica'] = $taksa->glavnica;
					$data_za_razduzenje['glavnica'] = 0;
					$taksa->update($data_za_razduzenje, $taksa->id);
					// unos zaduzenje_uplata
					$data_z_u['uplata_id'] = $ua->id;
					$data_z_u['uplata_datum'] = $ua->datum;
					$data_z_u['iznos'] = $taksa->glavnica;
					$data_z_u['iznos_prethodni'] = $taksa->glavnica;
					$model_z_u->insert($data_z_u);
					// osvezava se uplata (skida se iznos koji je otiso na razduzivanje takse sa avansa uplate)
					$ua->update(['avans' => $ua->avans - $taksa->glavnica], $ua->id);
					break; // prekida se foreach jer je razduzeno celo zaduzenje
				}
				else
				{
					// ako nije dovoljno da se razduzi celo zaduzenje-taksa
					// delimicno razduzivanje zaduzenja
					$data_za_razduzenje['razduzeno'] = 0;
					$data_za_razduzenje['korisnik_id_razduzio'] = null;
					$data_za_razduzenje['datum_razduzenja'] = null;
					$data_za_razduzenje['uplata_id'] = $ua->id;
					$data_za_razduzenje['iznos_razduzeno'] = $taksa->iznos_razduzeno + $ua->avans;
					$data_za_razduzenje['poslednja_glavnica'] = $taksa->glavnica;
					$data_za_razduzenje['glavnica'] = $taksa->glavnica - $ua->avans;
					$taksa->update($data_za_razduzenje, $taksa->id);
					// unos zaduzenje_uplata
					$data_z_u['uplata_id'] = $ua->id;
					$data_z_u['uplata_datum'] = $ua->datum;
					$data_z_u['iznos'] = $ua->avans;
					$data_z_u['iznos_prethodni'] = $taksa->glavnica;
					$model_z_u->insert($data_z_u);
					// osvezava se uplata (skida se iznos koji je otiso na razduzivanje takse sa avansa uplate)
					$ua->update(['avans' => 0], $ua->id);
				}
			}
		}

		$staraoc->update(['avans' => $staraoc->avans()], $staraoc->id);

		$zazduzenje = $model_zaduzenje->find($id);
		$this->log($this::DODAVANJE, $zazduzenje, ['tip', 'godina'], $zazduzenje);
		$this->flash->addMessage('success', 'Staraoc je uspešno zadužen odgovarajućom taksom.');
		return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $staraoc_id]));
	}
}
