<?php

namespace App\Controllers;

use App\Models\Staraoc;
use App\Models\Uplata;
use App\Models\Log;
use App\Models\Racun;
use App\Models\Zaduzenje;
use App\Models\ZaduzenjeUplata;
use App\Models\RacunUplata;

class UplataController extends Controller
{
	// pregled svih uplata
	public function getUplate($request, $response, array $args)
	{
		// dd(iznosSlovima(1112563.36));
		$id = (int) $args['id'];
		$staraoc = (new Staraoc())->find($id);

		$this->render($response, 'uplate.twig', compact('staraoc'));
	}

	// obrada uplate (razduzivanje)
	public function postUplata($request, $response)
	{
		$data = $request->getParams();
		$id = $data['staraoc_id'];

		// preuzimanje staraoca
		$staraoc = (new Staraoc())->find($id);

		// niz id-a zaduzenja (cekirane takse i zakupi na formi za razduzivanje)
		$zaduzenja_data = isset($data['razduzeno-zaduzenje']) ? $data['razduzeno-zaduzenje'] : [];

		// niz id-a racuna (cekirani racuni na formi za razduzivanje)
		$racuni_data = isset($data['razduzeno-racuni']) ? $data['razduzeno-racuni'] : [];

		unset($data['csrf_name']);
		unset($data['csrf_value']);
		unset($data['razduzeno-zaduzenje']);
		unset($data['razduzeno-racuni']);

		// pravila za proveru podataka
		$validation_rules = [
			'staraoc_id' => [
				'required' => true,
			],
			'uplata_iznos' => [
				'required' => true,
				'min' => 0,
			],
			'uplata_datum' => [
				'required' => true,
			],
			// da li je datum uplate isti ili veci od poslednjeg datuma uplate
			'uplata_datum' => [
				'min' => $staraoc->poslednjaUplata()->datum,
			],
		];

		// provera osnovnih podataka za uplatu
		$this->validator->validate($data, $validation_rules);

		// ako su prosledjeni neispravni podaci prekida se upis uplate
		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja uplate i razduživanja.');
			return $response->withRedirect($this->router->pathFor('transakcije.razduzivanje', ['id' => $id]));
		}

		// priprema za proveru visine iznosa koji se uplacuje
		$model_zaduzenje = new Zaduzenje();
		$model_racun = new Racun();
		// cekirana zaduzenja
		$zaduzenja = null;
		// cekirani racuni
		$racuni = null;
		// ukupan iznos za razduzenje cekiranih zaduzenja i racuna
		$iznos_za_razduzenje = 0;

		// proracun da li je prosledjeni iznos >= od zbira iznosa za razduzenje svih cekiranih zaduzenja i racuna

		// iznosi za razduzenje cekiranih zaduzenja
		if (!empty($zaduzenja_data))
		{
			$zad = implode(", ", $zaduzenja_data);
			$sql = "SELECT * FROM zaduzenja WHERE id IN ($zad);";
			$zaduzenja = $model_zaduzenje->fetch($sql);

			foreach ($zaduzenja as $zad)
			{
				$iznos_za_razduzenje += $zad->zaRazduzenje()['ukupno'];
			}
		}

		// iznosi za razduzenje cekiranih racuna
		if (!empty($racuni_data))
		{
			$rac = implode(", ", $racuni_data);
			$sql = "SELECT * FROM racuni WHERE id IN ($rac);";
			$racuni = $model_racun->fetch($sql);

			foreach ($racuni as $rn)
			{
				$iznos_za_razduzenje += $rn->zaRazduzenje()['ukupno'];
			}
		}

		// prosledjeni iznos uplate sa forme
		$iznos = (float) $data['uplata_iznos'];
		// razlika prosledjenog i potrebnog iznosa za razduzenje svih cekiranih zaduzenja i uplata
		$razlika = round($iznos - round($iznos_za_razduzenje, 2), 2);

		// ako je prosledjeni iznos manji od potrebnog iznosa za razduzivanje cekiranih stavki prekida se upis uplate
		if ($razlika < 0)
		{
			$this->flash->addMessage('danger', 'Iznos uplate nije dovoljan za razduživanje odabranih stavki.');
			return $response->withRedirect($this->router->pathFor('transakcije.razduzivanje', ['id' => $id]));
		}


		// =========================================================================================================================
		// POSTO SU SVE PROVERE PROSLE VRSI SE
		// RAZDUZIVANJE CEKIRANIH ZADUZENJA I RACUNA I
		// UPISUJE SE UPLATA
		// =========================================================================================================================

		// preuzimanje prijavljenog korisnika
		$korisnik_id = $this->auth->user()->id;

		// upisivanje uplate i razduzivanje cekiranih stavki

		// visak uplate za dodavanje na avans
		$visak_uplate = $razlika > 0 ? $razlika : 0;

		// podaci za uplatu
		$uplata_data = [
			'karton_id' => $staraoc->karton()->id,
			'staraoc_id' => $staraoc->id,
			'iznos' => $iznos, // ukupan iznos uplate
			'avans' => $visak_uplate, // visak posle razduzivanja svih cekiranih stavki
			'datum' => $data['uplata_datum'],
			'priznanica' => $data['uplata_priznanica'],
			'napomena' => $data['uplata_napomena'],
			'korisnik_id' => $korisnik_id,
		];

		// upisivanje i preuzimanje uplate
		$model_uplata = new Uplata();
		$model_uplata->insert($uplata_data);
		$uplata_id = $model_uplata->getLastId();
		// novo uneta uplata
		$uplata = $model_uplata->find($uplata_id);

		// dodaje se visak uplate na avans staraoca
		// TODO izbaciti avans iz staraoca i racunati ga iz avansa uplata staraoca
		$sql = "UPDATE staraoci SET avans = avans + {$visak_uplate} WHERE id = {$id};";
		$staraoc->run($sql);

		// opsti podaci za razduzivanje zaduzenja i racuna
		// vrse se samo potpuna razduzenja cekiranih stavki
		$data_za_razduzenje = [
			'razduzeno' => 1,
			'datum_razduzenja' => $data['uplata_datum'],
			'korisnik_id_razduzio' => $korisnik_id,
			'uplata_id' => $uplata_id,
			'glavnica' => 0,
			'datum_prispeca' => null,
		];

		$model_z_u = new ZaduzenjeUplata;
		$model_r_u = new RacunUplata;

		// opsti podaci za zaduzenje_uplata/racun_uplata
		$data_z_u = [
			'uplata_id' => $uplata_id,
			'uplata_datum' => $uplata->datum,
			'staraoc_id' => $staraoc->id,
			'avansno' => 0,
		];
		$data_r_u = [
			'uplata_id' => $uplata_id,
			'uplata_datum' => $uplata->datum,
			'staraoc_id' => $staraoc->id,
			'avansno' => 0,
		];

		// razduzivanje svih cekiranih zaduzenja i
		// unos u tabelu zaduzenje_uplata
		if (!empty($zaduzenja_data))
		{
			$zad = implode(", ", $zaduzenja_data);
			$sql = "SELECT * FROM zaduzenja WHERE id IN ($zad);";
			// sva zaduzenja koja se razduzuju
			$zaduzenja = $model_zaduzenje->fetch($sql);

			foreach ($zaduzenja as $zad)
			{
				// razduzivanje zaduzenja
				$data_za_razduzenje['iznos_razduzeno'] = $zad->zaRazduzenje()['ukupno'];
				$data_za_razduzenje['poslednja_glavnica'] = $zad->glavnica;
				$data_za_razduzenje['poslednji_datum_prispeca'] = $zad->datum_prispeca;
				$zid = (int) $zad->id;
				$model_zaduzenje->update($data_za_razduzenje, $zid);
				// unos zaduzenje_uplata
				$data_z_u['zaduzenje_id'] = $zid;
				$data_z_u['iznos'] = $zad->glavnica;
				$data_z_u['iznos_prethodni'] = $data_za_razduzenje['poslednja_glavnica'];
				$model_z_u->insert($data_z_u);
			}
		}

		if (!empty($racuni_data))
		{
			// svi racuni koji se razduzuju
			$rac = implode(", ", $racuni_data);
			$sql = "SELECT * FROM racuni WHERE id IN ($rac);";
			$racuni = $model_racun->fetch($sql);

			foreach ($racuni as $rn)
			{
				// razduzivanje racuna
				$data_za_razduzenje['iznos_razduzeno'] = $rn->zaRazduzenje()['ukupno'];
				$data_za_razduzenje['poslednja_glavnica'] = $rn->glavnica;
				$data_za_razduzenje['poslednji_datum_prispeca'] = $rn->datum_prispeca;
				$rid = (int) $rn->id;
				$model_racun->update($data_za_razduzenje, $rid);
				// TODO racun_uplata ili racuni da se prebace u zaduzenja
				// unos racun_uplata
				$data_r_u['racun_id'] = $rid;
				$data_r_u['iznos'] = $rn->glavnica;
				$data_r_u['iznos_prethodni'] = $data_za_razduzenje['poslednja_glavnica'];
				$model_r_u->insert($data_r_u);
			}
		}

		// logovanje
		$this->log($this::DODAVANJE, $uplata, ['karton_id', 'iznos', 'datum'], $uplata);
		$this->flash->addMessage('success', 'Uplata je uspešno sačuvana, a odabrane stavke su razdužene.');
		return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $id]));
	}

	public function postUplataBrisanje($request, $response)
	{
		// DOZVOLJENO JE BRISANJE SAMO POSLEDNJE UPLATE (zbog vracanja stanja u rikverc)

		// id uplate
		$id_uplate = (int) $request->getParam('modal_uplata_id');
		// uplata
		$uplata = (new Uplata())->find($id_uplate);
		// id_staraoca
		$staraoc_id = $uplata->staraoc()->id;
		// staraoc
		$staraoc = (new Staraoc())->find($staraoc_id);

		// poslednja uplata staraoca
		$poseldnja_uplata = $staraoc->poslednjaUplata()[0];

		// moze da brise samo admin
		if ($this->auth->user()->nivo !== 0)
		{
			$this->flash->addMessage('danger', "Brisanje uplate je omoguženo samo za administratore.");
			return $response->withRedirect($this->router->pathFor('uplate', ['id' => $staraoc_id]));
		}

		// ako nije poslednja uplata nije moguce brisanje
		if ($uplata->id !== $poseldnja_uplata->id)
		{
			$this->flash->addMessage('danger', "Može se brisati samo poslednja uplata.");
			return $response->withRedirect($this->router->pathFor('uplate', ['id' => $staraoc_id]));
		}

		// da li je uplata rate za reprogram
		if ($uplata->reprogram_id !== null)
		{ // kada je reprogram (uplacena je rata)
			// XXX: ovo jos nije pokrenuto
			$sql = "UPDATE reprogrami SET preostalo_rata = preostalo_rata + {$uplata->broj_rata}, korisnik_id_razduzio = NULL
					WHERE id = {$uplata->reprogram_id};";
			$uplata->run($sql);
			return;
		}

		// kada nije reprogram
		$sqlz = "SELECT * FROM zaduzenje_uplata WHERE uplata_id = :upid";
		$zaduzenje_uplata = new ZaduzenjeUplata();
		// sva zaduzenje_uplata za uplatu koja se brise
		$z_u = $zaduzenje_uplata->fetch($sqlz, [':upid' => $uplata->id]);
		$zaduzenje = new Zaduzenje();
		
		$sqlr = "SELECT * FROM racun_uplata WHERE uplata_id = :upid";
		$racun_uplata = new RacunUplata();
		// sva racun_uplata za uplatu koja se brise
		$r_u = $racun_uplata->fetch($sqlr, [':upid' => $uplata->id]);
		$racun = new Racun();

		foreach ($z_u as $zu)
		{
			// zaduzenje
			$zad = $zaduzenje->find($zu->zaduzenje_id);
			// podaci za vracanje zaduzenja na prethodno stanje
			$zad_data = [
				'glavnica' => $zu->iznos_prethodni,
				'iznos_razduzeno' => $zad->iznos_razduzeno - $zu->iznos,
				'datum_razduzenja' => null,
				'korisnik_id_razduzio' => null,
				'razduzeno' => 0,
				// XXX: ovo se inace koristilo pre tabele zaduzenje_uplata tako da nije bitno (treba obrisati)
				'uplata_id' => null,
				'avansno' => 0,
				'avans_iznos' => 0,
				'poslednja_glavnica' => 0,
			];
			// vracanje stanja zaduzenja na stanje pre uplate
			$zad->update($zad_data, $zad->id);
		}

		foreach ($r_u as $ru)
		{
			// racun
			$rac = $racun->find($ru->racun_id);
			// podaci za vracanje racuna na prethodno stanje
			$rac_data = [
				'glavnica' => $ru->iznos_prethodni,
				'iznos_razduzeno' => $rac->iznos_razduzeno - $ru->iznos,
				'datum_razduzenja' => null,
				'korisnik_id_razduzio' => null,
				'razduzeno' => 0,
				// XXX: ovo se inace koristilo pre tabele zaduzenje_uplata tako da nije bitno (treba obrisati)
				'uplata_id' => null,
				'avansno' => 0,
				'avans_iznos' => 0,
				'poslednja_glavnica' => 0,
			];
			// vracanje stanja zaduzenja na stanje pre uplate
			$rac->update($rac_data, $rac->id);
		}

		// update staraoc: staraoc->avans -= uplata->avans
		$staraoc->update(['avans' => $staraoc->avans - $uplata->avans], $staraoc_id);

		// brisanje svih zapisa za uplatu u tabeli zaduzenje_uplata
		$sql = "DELETE FROM zaduzenje_uplata WHERE uplata_id = :upid;";
		$zaduzenje_uplata->run($sql, [':upid' => $uplata->id]);

		// brisanje svih zapisa za uplatu u tabeli racun_uplata
		$sql = "DELETE FROM racun_uplata WHERE uplata_id = :upid;";
		$racun_uplata->run($sql, [':upid' => $uplata->id]);

		// brisanje same uplate
		$success = $uplata->deleteOne($id_uplate);

		if ($success)
		{ // ako je uspesno obrisana uplata
			$this->log($this::BRISANJE, $uplata, ['karton_id', 'datum', 'iznos', 'priznanica'], $uplata);
			$this->flash->addMessage('success', "Uplata je uspešno obrisana.");
			return $response->withRedirect($this->router->pathFor('uplate', ['id' => $staraoc_id]));
		}

		// greska prilikom brisanja uplate
		$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja uplate.");
		return $response->withRedirect($this->router->pathFor('uplate', ['id' => $staraoc_id]));
	}

	// FIXME ovo je stara metoda za razduzivanje avansom
	// trenutno se ne koristi
	public function postVisak($request, $response)
	{
		$id = (int) $request->getParam('staraoc_id');
		$staraoc = (new Staraoc())->find($id);

		if ($staraoc->imaAvansNerazduzen() && $staraoc->aktivan == 1)
		{
			// iznos viska para
			$iznos = (float) $staraoc->avans;

			// ovde napraviti laznu uplatu
			$model_uplata = new Uplata();
			$model_uplata->insert([
				'karton_id' => $staraoc->karton()->id,
				'staraoc_id' => $staraoc->id,
				'datum' => date('Y-m-d'),
				'iznos' => round($iznos, 2),
				'korisnik_id' => $this->auth->user()->id,
				'napomena' => 'automatsko razduživanje viška uplate',
				'visak' => 1,
			]);
			$uplata_id = $model_uplata->getLastId();

			// sva nerazduzena zaduzenja
			$zaduzenja = $staraoc->zaduzenaZaduzenja();
			// svi nerazduzeni racuni
			$racuni = $staraoc->zaduzeniRacuni();

			// ostatak viska
			$ostatak = $iznos;

			// 1. razduzivanje zaduzenih taksi i zakupa
			foreach ($zaduzenja as $zad)
			{
				$iznos = $ostatak;
				$razlika = (float) $ostatak - $zad->zaRazduzenje()['ukupno'];

				if ($razlika < 0) // ako je delimicno razduzenje
				{
					// delimcno razduzenje zaduzenja
					$zad->update([
						'poslednja_glavnica' => $zad->glavnica,
						'poslednji_datum_prispeca' => $zad->datum_prispeca,
						'glavnica' => $zad->glavnica - $iznos,
						'iznos_razduzeno' => $zad->iznos_razduzeno + $iznos,
						'datum_prispeca' => $zad->datum_prispeca === null ? null : date('Y-m-d'),
						'napomena' => $zad->napomena . "\nautomatsko razduživanje viška uplate",
						'uplata_id' => $uplata_id,
					], $zad->id);
					$ostatak = 0;
					break;
				}
				else
				{
					// potpino razduzenja zaduzenja
					$zad->update([
						'poslednja_glavnica' => $zad->glavnica,
						'poslednji_datum_prispeca' => $zad->datum_prispeca,
						'glavnica' => 0,
						'datum_prispeca' => null,
						'iznos_razduzeno' => $zad->iznos_zaduzeno,
						'razduzeno' => 1,
						'datum_razduzenja' => date('Y-m-d'),
						'korisnik_id_razduzio' => $this->auth->user()->id,
						'napomena' => $zad->napomena . "\nautomatsko razduživanje viška uplate",
						'uplata_id' => $uplata_id,
					], $zad->id);
					$ostatak = $razlika;
				}
			}

			// 2. ako postoji ostatak prelazi se na razduzivanje racuna
			if ($ostatak > 0)
			{
				foreach ($racuni as $rn)
				{
					$iznos = $ostatak;
					$razlika = (float) $ostatak - $rn->zaRazduzenje()['ukupno'];

					if ($razlika < 0) // ako je delimicno razduzenje
					{
						// delimcno razduzenje racuna
						$rn->update([
							'poslednja_glavnica' => $rn->glavnica,
							'poslednji_datum_prispeca' => $rn->datum_prispeca,
							'glavnica' => $rn->glavnica - $iznos,
							'iznos_razduzeno' => $rn->iznos_razduzeno + $iznos,
							'datum_prispeca' => $rn->datum_prispeca === null ? null : date('Y-m-d'),
							'napomena' => $rn->napomena . "\nautomatsko razduživanje viška uplate",
							'uplata_id' => $uplata_id,
						], $rn->id);
						$ostatak = 0;
						break;
					}
					else
					{
						// potpino razduzenje racuna
						$rn->update([
							'poslednja_glavnica' => $rn->glavnica,
							'poslednji_datum_prispeca' => $rn->datum_prispeca,
							'glavnica' => 0,
							'datum_prispeca' => null,
							'iznos_razduzeno' => $rn->iznos,
							'razduzeno' => 1,
							'datum_razduzenja' => date('Y-m-d'),
							'korisnik_id_razduzio' => $this->auth->user()->id,
							'napomena' => $rn->napomena . "\nautomatsko razduživanje viška uplate",
							'uplata_id' => $uplata_id,
						], $rn->id);
						$ostatak = $razlika;
					}
				}
			}

			// upisivanje ostatka na avans staraoca
			$ostatak = round($ostatak > 0 ? $ostatak : 0, 2);
			$staraoc->update(['avans' => $ostatak], $staraoc->id);
		}

		$modelLoga = new Log();
		$datal['tip'] = "dodavanje";
		$datal['opis'] = "Potvrđena preraspodela preostalih sredstava za razduživanje taksi i zakupa. Karton: "
			. $staraoc->karton()->id . ", staraoc: " . $staraoc->id . ", datum: " . date('Y-m-d');
		$datal['korisnik_id'] = $this->auth->user()->id;
		$modelLoga->insert($datal);
		$this->flash->addMessage('success', 'Višak novca je uspešno raspoređen na razduživanje taksi i zakupa.');
		return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $id]));
	}

	// XXX umesto prethodne koristi se ova metoda
	// razduzivanje avansa na nerazduzeno zaduzenje
	public function postVisakUnos($request, $response)
	{
		$data = $request->getParams();

		// niz id-a zaduzenja
		$zaduzenja_data = isset($data['razduzeno-zaduzenje']) ? $data['razduzeno-zaduzenje'] : [];

		// niz id-a racuna
		$racuni_data = isset($data['razduzeno-racuni']) ? $data['razduzeno-racuni'] : [];

		unset($data['csrf_name']);
		unset($data['csrf_value']);
		unset($data['razduzeno-zaduzenje']);
		unset($data['razduzeno-racuni']);

		$staraoc_id = $data['staraoc_id'];

		$validation_rules = [
			'staraoc_id' => [
				'required' => true,
			],
			'visak_iznos' => [
				'required' => true,
				'min' => 0,
			],
		];

		$this->validator->validate($data, $validation_rules);

		// proveriti da li je odabrano samo jedno zaduzenje/racun
		$br = count($zaduzenja_data) + count($racuni_data);
		if ($br != 1)
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom razduživanja. Može se razdužiti samo jedna stavka.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $staraoc_id]));
		}

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom razduživanja.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $staraoc_id]));
		}

		$staraoc = (new Staraoc())->find($staraoc_id);
		// $visak_iznos = $staraoc->avans();
		$korisnik_id = $this->auth->user()->id;

		if (count($zaduzenja_data) == 1) // ako je zaduzenje (taksa/zakup)
		{
			$id = (int) $zaduzenja_data[0];
			$zaduzenje = (new Zaduzenje())->find($id);
			$uplate_sa_avansom = $staraoc->uplateSaAvansom();
			$model_z_u = new ZaduzenjeUplata;

			$data_za_razduzenje = [];
			$data_z_u = [
				'zaduzenje_id' => $zaduzenje->id,
				'staraoc_id' => $staraoc->id,
			];

			foreach ($uplate_sa_avansom as $ua)
			{
				// za svaku uplatu se proveri da li je dovoljna za razduzivanje zaduzenja
				if ($ua->avans >= $zaduzenje->glavnica) // ili glavnica
				{
					// ako je dovoljno da se razduzi celo zaduzenje
					// razduzivanje celog zaduzenja
					$data_za_razduzenje['razduzeno'] = 1;
					$data_za_razduzenje['korisnik_id_razduzio'] = $korisnik_id;
					$data_za_razduzenje['datum_razduzenja'] = date('Y-m-d');
					$data_za_razduzenje['uplata_id'] = $ua->id;
					$data_za_razduzenje['iznos_razduzeno'] = $zaduzenje->iznos_zaduzeno;
					$data_za_razduzenje['poslednja_glavnica'] = $zaduzenje->glavnica;
					$data_za_razduzenje['glavnica'] = 0;
					$zaduzenje->update($data_za_razduzenje, $zaduzenje->id);
					// unos zaduzenje_uplata
					$data_z_u['uplata_id'] = $ua->id;
					$data_z_u['uplata_datum'] = $ua->datum;
					$data_z_u['iznos'] = $zaduzenje->glavnica;
					$data_z_u['iznos_prethodni'] = $zaduzenje->glavnica;
					$model_z_u->insert($data_z_u);
					// osvezava se uplata (skida se iznos koji je otiso na razduzivanje zaduzenja sa avansa uplate)
					$ua->update(['avans' => $ua->avans - $zaduzenje->glavnica], $ua->id);
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
					$data_za_razduzenje['iznos_razduzeno'] = $zaduzenje->iznos_razduzeno + $ua->avans;
					$data_za_razduzenje['poslednja_glavnica'] = $zaduzenje->glavnica;
					$data_za_razduzenje['glavnica'] = $zaduzenje->glavnica - $ua->avans;
					$zaduzenje->update($data_za_razduzenje, $zaduzenje->id);
					// unos zaduzenje_uplata
					$data_z_u['uplata_id'] = $ua->id;
					$data_z_u['uplata_datum'] = $ua->datum;
					$data_z_u['iznos'] = $ua->avans;
					$data_z_u['iznos_prethodni'] = $zaduzenje->glavnica;
					$model_z_u->insert($data_z_u);
					// osvezava se uplata (skida se iznos koji je otiso na razduzivanje takse sa avansa uplate)
					$ua->update(['avans' => 0], $ua->id);
				}
			}
		}
		else // ako nije zaduzenje onda je racun
		{
			$id = (int) $racuni_data[0];
			$racun = (new Racun())->find($id);
			$uplate_sa_avansom = $staraoc->uplateSaAvansom();
			$model_r_u = new RacunUplata();

			$data_za_razduzenje = [];
			$data_r_u = [
				'racun_id' => $racun->id,
				'staraoc_id' => $staraoc->id,
			];

			foreach ($uplate_sa_avansom as $ua)
			{
				// za svaku uplatu se proveri da li je dovoljna za razduzivanje racuna
				if ($ua->avans >= $racun->glavnica) // ili glavnica
				{
					// ako je dovoljno da se razduzi ceo racun
					// razduzivanje celog racuna
					$data_za_razduzenje['razduzeno'] = 1;
					$data_za_razduzenje['korisnik_id_razduzio'] = $korisnik_id;
					$data_za_razduzenje['datum_razduzenja'] = date('Y-m-d');
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
					// osvezava se uplata (skida se iznos koji je otiso na razduzivanje racuna sa avansa uplate)
					$ua->update(['avans' => $ua->avans - $racun->glavnica], $ua->id);
					break; // prekida se foreach jer je razduzeno celo zaduzenje
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

		$this->flash->addMessage('success', 'Delimično razduživanje je uspešno odrađeno.');
		return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $staraoc_id]));
	}
}
