<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Staraoc;
use App\Models\Zaduzenje;
use App\Models\Racun;
use App\Models\Reprogram;
use App\Models\Uplata;
use App\Models\Log;

class TransakcijeController extends Controller
{

	public function getKartonPregled($request, $response, $args)
	{
		$staraoc_id = $args['id'];
		$zaduzenost = isset($args['z']) ? (int) $args['z'] : 0;

		if ($zaduzenost < 0 | $zaduzenost > 2)
		{
			$zaduzenost = 0;
		}

		$staraoc = (new Staraoc())->find($staraoc_id);
		$broj_uplata = count($staraoc->uplate());


		// TODO izbrisati visak posto se ne koristi na formi/pogledu
		$visak = [];

		// ako postoji visak/avans i postoje zaduzenja koja nisu razduzena
		// visak/avans mora da se prebaci na neko nerazduzeno zaduzenje
		if ($staraoc->imaAvansNerazduzen() && $staraoc->aktivan == 1)
		{
			// sva nerazduzena zaduzenja
			$zaduzenja = $staraoc->zaduzenaZaduzenja();
			// svi nerazduzeni racuni
			$racuni = $staraoc->zaduzeniRacuni();

			// iznos viska para
			$iznos = (float) $staraoc->avans();
			// ostatak viska
			$ostatak = $iznos;


			// pocetni parametri

			// broj celih taksi koje mogu da se razduze
			$br_taksi = 0;
			// broj celih zakupa koje mogu da se razduze
			$br_zakupa = 0;
			// taksa ili zakup koji moze delimicno da se razduzi
			$deo = 0;
			// vrsta (taksa ili zakup) za delimicno razduzivanje
			$vrsta = '';
			// godina takse ili zakupa za delimicno razduzivanje
			$godina = 0;
			// godine taksi koje mogu cele da se razduze
			$godine_taksi = '';
			// godine zakupa koji mogu celi da se razduze
			$godine_zakupa = '';

			// racuni (ako ostane para posle razduzivanja taksi i zakupa)
			// broj celih racuna koji mogu da se razduze
			$br_racuna = 0;
			// iznos delimicnog razduzenja racuna
			$deo_racuna = 0;
			// broj racuna za delimicno razduzivanje
			$broj_racuna = '';
			// brojevi racuna koji mogu celi da se razduze
			$brojevi_racuna = '';

			// ostatak posle razduzivanja svih taksi, zakupa i racuna
			$ost = 0;

			// 1. razduzivanje zaduzenih taksi i zakupa
			foreach ($zaduzenja as $zad)
			{
				$iznos = $ostatak;
				$razlika = (float) $ostatak - $zad->zaRazduzenje()['ukupno'];

				if ($razlika < 0) // ako je delimicno razduzenje
				{
					$deo = $iznos;
					$vrsta = $zad->tip;
					$godina = $zad->godina;
					$ostatak = 0;
					break;
				}
				else
				{
					if ($zad->tip === 'taksa') // ako se razduzuje cela taksa
					{
						$br_taksi++;
						$godine_taksi .= ', ' . $zad->godina;
						$ostatak = $razlika;
					}
					elseif ($zad->tip === 'zakup') // ako se razduzuje ceo zakup
					{
						$br_zakupa++;
						$godine_zakupa .= ', ' . $zad->godina;
						$ostatak = $razlika;
					}
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
						$deo_racuna = $iznos;
						$broj_racuna = $rn->broj;
						$ostatak = 0;
						break;
					}
					else
					{
						$br_racuna++;
						$brojevi_racuna .= ', ' . $rn->broj;
						$ostatak = $razlika;
					}
				}
			}

			$visak = [
				'br_taksi' => $br_taksi,
				'godine_taksi' => trim($godine_taksi, ','),
				'br_zakupa' => $br_zakupa,
				'godine_zakupa' => trim($godine_zakupa, ','),
				'godina' => $godina,
				'vrsta' => $vrsta,
				'deo' => round($deo, 2),
				'br_racuna' => $br_racuna,
				'deo_racuna' => round($deo_racuna, 2),
				'broj_racuna' => $broj_racuna,
				'brojevi_racuna' => trim($brojevi_racuna, ','),
				'ostatak' => round($ostatak > 0 ? $ostatak : 0, 2), // ostatak posle razduzenja svega
			];
		}

		$this->render($response, 'transakcije_pregled.twig', compact('staraoc', 'broj_uplata', 'zaduzenost', 'visak'));
	}

	public function getKartonPregledStampa($request, $response, $args)
	{
		$id = (int) $args['id'];
		$staraoc = (new Staraoc())->find($id);
		$cene = new Cena();
		$this->render($response, 'print/transakcije_pregled.twig', compact('staraoc', 'cene'));
	}

	public function getKartonRazduzivanje($request, $response, $args)
	{
		$id = (int) $args['id'];
		$staraoc = (new Staraoc())->find($id);

		$cene = new Cena();

		$this->render($response, 'transakcije_razduzivanje.twig', compact('staraoc', 'cene'));
	}

	public function getZaduzivanje($request, $response)
	{
		$model = new Cena();
		$taksa = $model->taksa();
		$zakup = $model->zakup();

		$zaduzena_godina = false;
		$upozorenje = '';

		$trenutna_godina = (int) date('Y');
		$prethodna_godina = $trenutna_godina - 1;

		$model_karton = new Karton();
		$model_zaduzenje = new Zaduzenje();

		$sql_broj_kartona = "SELECT COUNT(*) AS broj_kartona FROM kartoni WHERE aktivan = 1;";
		$sql_broj_zaduzenja = "SELECT COUNT(*) AS broj_zaduzenja FROM zaduzenja WHERE godina = {$prethodna_godina};";

		$broj_kartona = $model_karton->fetch($sql_broj_kartona)[0]->broj_kartona;
		$broj_zaduzenja = $model_zaduzenje->fetch($sql_broj_zaduzenja)[0]->broj_zaduzenja;

		// vise 75% kartona je zaduzeno za godinu
		if ($broj_zaduzenja > ($broj_kartona * 1.5))
		{
			$upozorenje = "Većina kartona je zadužena za {$prethodna_godina}. godinu";
			$zaduzena_godina = true;
		}
		else
		{
			$upozorenje = "Većina kartona nije zadužena za {$prethodna_godina}. godinu";
			$zaduzena_godina = false;
		}

		$this->render($response, 'zaduzivanje.twig', compact('taksa', 'zakup', 'upozorenje', 'zaduzena_godina', 'trenutna_godina'));
	}

	public function postZaduzivanje($request, $response)
	{
		$staraoci_sa_nerazduzenim_avansom = count((new Staraoc())->sviSaraociSaNerazduzenimAvansom());

		if ($staraoci_sa_nerazduzenim_avansom > 0)
		{
			$this->flash->addMessage('danger', 'Postoje staraoci koji imaju avans i nerazdužena zaduženja!');
			return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje'));
		}

		/*
			DODATI BROJ RACUNA

			prilikom zaduzivanja skidati novac sa avansa staraoca
		*/

		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);

		$trenutna_godina = (int) date('Y');

		$validation_rules = [
			'taksa' => [
				'required' => true,
			],
			'zakup' => [
				'required' => true,
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
				'min' => $trenutna_godina - 1,
				'max' => $trenutna_godina,
			],
		];

		$this->validator->validate($data, $validation_rules);

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom zaduživanja kartona.');
			return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje'));
		}
		else
		{
			$model_karton = new Karton();
			$model_zaduzenja = new Zaduzenje();

			$taksa = (float) $data['taksa'];
			$zakup = (float) $data['zakup'];
			$godina = (int) $data['godina'];
			$datum_zaduzenja = date('Y-m-d', strtotime($data['datum_zaduzenja']));
			$datum_prispeca = date('Y-m-d', strtotime($data['datum_prispeca']));

			$kartoni = $model_karton->sviAktivni();
			$pdo = $model_karton->getDb()->getPDO();
			$sql = "INSERT INTO `zaduzenja`
					(karton_id, staraoc_id, tip, godina, iznos_zaduzeno, glavnica, iznos_razduzeno, razduzeno, datum_zaduzenja,
					datum_razduzenja, datum_prispeca, korisnik_id_zaduzio, korisnik_id_razduzio, napomena, avansno, avans_iznos)
                    VALUES
					(:karton_id, :staraoc_id, :tip, :godina, :iznos_zaduzeno, :glavnica, :iznos_razduzeno, :razduzeno, :datum_zaduzenja,
					:datum_razduzenja, :datum_prispeca, :korisnik_id_zaduzio, :korisnik_id_razduzio, :napomena, :avansno, :avans_iznos);";
			$stmt = $pdo->prepare($sql);

			$pdo->beginTransaction();

			foreach ($kartoni as $karton)
			{
				$staraoci = $karton->aktivniStaraoci();
				$br_mesta = $karton->broj_mesta;
				$br_staraoca = count($staraoci);

				foreach ($staraoci as $staraoc)
				{
					// ovse se skida sa avansa
					$avans = $staraoc->avans;

					// proveriti da li je zaduzen taksom za godinu
					$tip = 1;
					$sql_taksa = "SELECT COUNT(*) AS br_taksi FROM zaduzenja WHERE karton_id = {$karton->id}
                                    AND staraoc_id = {$staraoc->id} AND tip = {$tip} AND godina = {$godina}";
					$br_taksi = $model_zaduzenja->fetch($sql_taksa)[0]->br_taksi;

					if ($br_taksi === 0)
					{
						// odrediti taksu
						$iznos_takse = (float) ($taksa * $br_mesta / $br_staraoca);

						$podaci = [
							':karton_id' => $karton->id,
							':staraoc_id' => $staraoc->id,
							':tip' => 'taksa',
							':godina' => $godina,
							':iznos_zaduzeno' => $iznos_takse,
							':glavnica' => $iznos_takse,
							':iznos_razduzeno' => 0,
							':razduzeno' => 0,
							':datum_zaduzenja' => $datum_zaduzenja,
							':datum_razduzenja' => null,
							':datum_prispeca' => $datum_prispeca,
							':korisnik_id_zaduzio' => $this->auth->user()->id,
							':korisnik_id_razduzio' => null,
							':napomena' => "Automatsko zaduživanje za {$godina}. godinu",
							':avansno' => 0,
							':avans_iznos' => 0,
						];

						/*
							1. ako je avans manji od iznos_takse
								- umanjiti glavnicu za avans
								- postaviti avans na 0
							2. ako je avans veci od iznos_takse
								- razduziti taksu
								- umanjiti avans za iznos_takse
						*/

						if ($avans > 0 && $avans < $iznos_takse)
						{
							$podaci[':glavnica'] -= $avans;
							$podaci[':iznos_razduzeno'] = $avans;
							$podaci[':avans_iznos'] = $avans;
							$avans = 0;
							$podaci[':avansno'] = 1;
						}

						if ($avans > $iznos_takse)
						{
							$avans -= $iznos_takse;
							$podaci[':glavnica'] = 0;
							$podaci[':avansno'] = 1;
							$podaci[':iznos_razduzeno'] = $iznos_takse;
							$podaci[':avans_iznos'] = $iznos_takse;
							$podaci[':razduzeno'] = 1;
							$podaci[':datum_razduzenja'] = $datum_zaduzenja;
							$podaci[':korisnik_id_razduzio'] = $this->auth->user()->id;
						}

						$stmt->execute($podaci);
					}

					// proveriti da li je zaduzen zakupom
					$tip = 2;
					$sql_zakup = "SELECT COUNT(*) AS br_zakupa FROM zaduzenja WHERE karton_id = {$karton->id}
                                    AND staraoc_id = {$staraoc->id} AND tip = {$tip} AND godina = {$godina}";
					$br_zakupa = $model_zaduzenja->fetch($sql_zakup)[0]->br_zakupa;

					if ($br_zakupa === 0)
					{
						// odrediti zakup
						$iznos_zakupa = (float) ($zakup * $br_mesta / $br_staraoca);

						$podaci = [
							':karton_id' => $karton->id,
							':staraoc_id' => $staraoc->id,
							':tip' => 'zakup',
							':godina' => $godina,
							':iznos_zaduzeno' => $iznos_zakupa,
							':glavnica' => $iznos_zakupa,
							':iznos_razduzeno' => 0,
							':razduzeno' => 0,
							':datum_zaduzenja' => $datum_zaduzenja,
							':datum_razduzenja' => null,
							':datum_prispeca' => $datum_prispeca,
							':korisnik_id_zaduzio' => $this->auth->user()->id,
							':korisnik_id_razduzio' => null,
							':napomena' => "Automatsko zaduživanje za {$godina}. godinu",
							':avansno' => 0,
							':avans_iznos' => 0,

						];

						/*
							1. ako je avans manji od iznos_zakupa
								- umanjiti glavnicu za avans
								- postaviti avans na 0
							2. ako je avans veci od iznos_zakupa
								- razduziti taksu
								- umanjiti avans za iznos_zakupa
						*/

						if ($avans > 0 && $avans < $iznos_zakupa)
						{
							$podaci[':glavnica'] -= $avans;
							$podaci[':iznos_razduzeno'] = $avans;
							$podaci[':avans_iznos'] = $avans;
							$avans = 0;
							$podaci[':avansno'] = 1;
						}

						if ($avans > $iznos_zakupa)
						{
							$avans -= $iznos_zakupa;
							$podaci[':glavnica'] = 0;
							$podaci[':avansno'] = 1;
							$podaci[':avans_iznos'] = $iznos_takse;
							$podaci[':iznos_razduzeno'] = $iznos_zakupa;
							$podaci[':razduzeno'] = 1;
							$podaci[':datum_razduzenja'] = $datum_zaduzenja;
							$podaci[':korisnik_id_razduzio'] = $this->auth->user()->id;
						}

						$stmt->execute($podaci);
					}

					// upisati avans staraoca
					$model_staroaci = new Staraoc();
					$sql_avans = "UPDATE staraoci SET avans = {$avans} WHERE id = {$staraoc->id};";
					$model_staroaci->run($sql_avans);
				}
			}

			$pdo->commit();
			$modelLoga = new Log();
			$datal['tip'] = "dodavanje";
			$datal['opis'] = "Zaduživanje za godinu: " . $godina;
			$datal['korisnik_id'] = $this->auth->user()->id;
			$modelLoga->insert($datal);
			$this->flash->addMessage('success', 'Svi aktivni kartoni su uspešno zaduženi.');
			return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje'));
		}
	}

	

	public function getOpomene($request, $response)
	{
		$model_karton = new Karton();
		$kartoni = $model_karton->sviAktivni();

		$this->render($response, 'print/opomene.twig', compact('kartoni'));
	}

	public function postZaduzenjeBrisanje($request, $response)
	{
		$id = (int) $request->getParam('modal_zaduzenje_id');
		$zaduzenje = (new Zaduzenje())->find($id);

		// ne moze da se brise ako je
		// razduzeno = 1 ili
		// iznos_razduzeno > 0 ili
		// reprogram_id != null
		if ($zaduzenje->razduzeno === 1 || $zaduzenje->iznos_razduzeno > 0 || $zaduzenje->reprogram_id != null)
		{
			$this->flash->addMessage('danger', "Postoje transakcije vezane za ovo zaduženje.");
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $zaduzenje->staraoc()->id]));
		}

		$success = $zaduzenje->deleteOne($id);

		if ($success)
		{
			$this->flash->addMessage('success', "Zaduženje je uspešno obrisano.");
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $zaduzenje->staraoc()->id]));
		}
		else
		{
			$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja zaduženja.");
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $zaduzenje->staraoc()->id]));
		}
	}

	public function postSveBrisanje($request, $response)
	{
	}
}
