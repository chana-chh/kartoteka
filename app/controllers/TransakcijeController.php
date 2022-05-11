<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Staraoc;
use App\Models\Zaduzenje;
use App\Models\Racun;
use App\Models\Reprogram;
use App\Models\Uplata;

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

		// ako ima privremeni_saldo (pretekle mu pare posle razduzenja)
		// preuzeti sva nerazduzena zaduzenja
		// sloziti po godinama ASC
		// krenuti redom i smanjivati iznos
		// razduzivati stare godine
		// ako nema vise sarih godin zaduzivati/razduzivati nove
		// od toga napraviti izvestaj
		// istu foru koristiti kod razduzivanja viska para
		$visak = [];
		if ($staraoc->privremeni_saldo > 0)
		{
			// sva nerazduzena zaduzenja
			$zaduzenja = $staraoc->zaduzenaZaduzenja();

			// iznos viska para
			$iznos = (float) $staraoc->privremeni_saldo;
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

			foreach ($zaduzenja as $zad)
			{
				$iznos = $ostatak;
				$razlika = (float) $ostatak - $zad->zaRazduzenje();

				if ($razlika < 0)
				{
					$deo = $iznos;
					$vrsta = $zad->tip;
					$godina = $zad->godina;
					$ostatak = 0;
					break;
				}
				else
				{
					if ($zad->tip === 'taksa')
					{
						$br_taksi++;
						$godine_taksi .= ', ' . $zad->godina;
						$ostatak = $razlika;
					}
					elseif ($zad->tip === 'zakup')
					{
						$br_zakupa++;
						$godine_zakupa .= ', ' . $zad->godina;
						$ostatak = $razlika;
					}
				}
			}

			// dovde je za stare dugove


			// ako postoji ostatak dodaju se nova zaduzenja taksa, zakup, taksa, zakup
			// dok se ne potrosi ostatak

			if ($ostatak > 0)
			{
				// za nova zaduzenja koja ne postoje

				// broj celih taksi koje mogu da se razduze
				$n_br_taksi = 0;
				// broj celih zakupa koje mogu da se razduze
				$n_br_zakupa = 0;
				// taksa ili zakup koji moze delimicno da se razduzi
				$n_deo = 0;
				// vrsta (taksa ili zakup) za delimicno razduzivanje
				$n_vrsta = '';
				// godina takse ili zakupa za delimicno razduzivanje
				$n_godina = 0;
				// godine taksi koje mogu cele da se razduze
				$n_godine_taksi = '';
				// godine zakupa koji mogu celi da se razduze
				$n_godine_zakupa = '';

				// pocetna godina za nove takse
				$sql = "SELECT MAX(godina) AS max_godina FROM zaduzenja WHERE tip = 'taksa' AND staraoc_id = {$staraoc->id};";
				$godina_za_taksu = (int) $staraoc->fetch($sql)[0]->max_godina + 1;
				// pocetna godina za nove zakupe
				$sql = "SELECT MAX(godina) AS max_godina FROM zaduzenja WHERE tip = 'zakup' AND staraoc_id = {$staraoc->id};";
				$godina_za_zakup = (int) $staraoc->fetch($sql)[0]->max_godina + 1;

				// odrediti manji pocetnu godinu i krenuti odatle (taksa ili zakup)
				// da li voziti isto dok se ne stigne druga pocetna godina ili
				// samo terati taksa, zakup

				$radi_taksu = $godina_za_taksu <= $godina_za_zakup ? true : false;
				// petlja dok ima ostatka
				do
				{
					$iznos = $ostatak;

					if ($radi_taksu)
					{
						$razlika = (float) $ostatak - $staraoc->taksaZaGodinu();
					}
					else
					{
						$razlika = (float) $ostatak - $staraoc->zakupZaGodinu();
					}

					if ($razlika < 0)
					{
						$n_deo = $iznos;

						if ($radi_taksu)
						{
							$n_vrsta = 'taksa';
							$n_godina = $godina_za_taksu;
						}
						else
						{
							$n_vrsta = 'zakup';
							$n_godina = $godina_za_zakup;
						}

						$ostatak = 0;
					}
					else
					{
						if ($radi_taksu)
						{
							$n_br_taksi++;
							$n_godine_taksi .= ', ' . $godina_za_taksu;
							$ostatak = $razlika;
							$godina_za_taksu++;
						}
						else
						{
							$n_br_zakupa++;
							$n_godine_zakupa .= ', ' . $godina_za_zakup;
							$ostatak = $razlika;
							$godina_za_zakup++;
						}
					}
					$radi_taksu = $godina_za_taksu <= $godina_za_zakup ? true : false;
				} while ($ostatak > 0);
			}

			$visak = [
				'br_taksi' => $br_taksi,
				'godine_taksi' => trim($godine_taksi, ','),
				'br_zakupa' => $br_zakupa,
				'godine_zakupa' => trim($godine_zakupa, ','),
				'godina' => $godina,
				'vrsta' => $vrsta,
				'deo' => round($deo, 2),
				'n_br_taksi' => $n_br_taksi,
				'n_godine_taksi' => trim($n_godine_taksi, ','),
				'n_br_zakupa' => $n_br_zakupa,
				'n_godine_zakupa' => trim($n_godine_zakupa, ','),
				'n_godina' => $n_godina,
				'n_vrsta' => $n_vrsta,
				'n_deo' => round($n_deo, 2),
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

		$curr_year = (int) date('Y');

		$model_karton = new Karton();
		$model_zaduzenje = new Zaduzenje();

		$sql_broj_kartona = "SELECT COUNT(*) AS broj_kartona FROM kartoni WHERE aktivan = 1;";
		$sql_broj_zaduzenja = "SELECT COUNT(*) AS broj_zaduzenja FROM zaduzenja WHERE godina = {$curr_year};";

		$broj_kartona = $model_karton->fetch($sql_broj_kartona)[0]->broj_kartona;
		$broj_zaduzenja = $model_zaduzenje->fetch($sql_broj_zaduzenja)[0]->broj_zaduzenja;

		// vise 75% kartona je zaduzeno za godinu
		if ($broj_zaduzenja > ($broj_kartona * 1.5))
		{
			$upozorenje = "Većina kartona je zadužena za tekuću godinu ({$curr_year})";
			$zaduzena_godina = true;
		}
		else
		{
			$upozorenje = "Većina kartona nije zadužena za tekuću godinu ({$curr_year})";
			$zaduzena_godina = false;
		}

		$this->render($response, 'zaduzivanje.twig', compact('taksa', 'zakup', 'upozorenje', 'zaduzena_godina'));
	}

	public function postZaduzivanje($request, $response)
	{
		// onemoguciti zaduzivanje i razduzivanje neaktivnih staraoca
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);

		$cur_year = (int) date('Y');

		$validation_rules = [
			'taksa' => [
				'required' => true,
			],
			'zakup' => [
				'required' => true,
			],
			'godina' => [
				'required' => true,
				'min' => $cur_year - 1,
				'max' => $cur_year + 1,
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

			$taksa =  (float) $data['taksa'];
			$zakup =  (float) $data['zakup'];
			$godina = (int) $data['godina'];

			$podaci = [
				':karton_id' => 0,
				':staraoc_id' => 0,
				':tip' => 'taksa',
				':godina' => $godina,
				':iznos_zaduzeno' => 0,
				':iznos_razduzeno' => 0,
				':razduzeno' => 0,
				':datum_zaduzenja' => date('Y-m-d'),
				':korisnik_id_zaduzio' => $this->auth->user()->id,
			];

			$kartoni = $model_karton->sviAktivni();
			$pdo = $model_karton->getDb()->getPDO();
			$sql = "INSERT INTO `zaduzenja` (karton_id, staraoc_id, tip, iznos_zaduzeno, iznos_razduzeno, godina, razduzeno, datum_zaduzenja, korisnik_id_zaduzio)
                    VALUES (:karton_id, :staraoc_id, :tip, :iznos_zaduzeno, :iznos_razduzeno, :godina, :razduzeno, :datum_zaduzenja, :korisnik_id_zaduzio);";
			$stmt = $pdo->prepare($sql);

			$pdo->beginTransaction();

			foreach ($kartoni as $karton)
			{
				$staraoci = $karton->aktivniStaraoci();
				$br_mesta = $karton->broj_mesta;
				$br_staraoca = count($staraoci);

				foreach ($staraoci as $staraoc)
				{
					// proveriti da li je zaduzen taksom
					$tip = 1;
					$sql_taksa = "SELECT COUNT(*) AS br_taksi FROM zaduzenja WHERE karton_id = {$karton->id}
                                    AND staraoc_id = {$staraoc->id} AND tip = {$tip} AND godina = {$godina}";
					$br_taksi = $model_zaduzenja->fetch($sql_taksa)[0]->br_taksi;

					if ($br_taksi === 0)
					{
						// odrediti taksu
						$iznos_takse = (float) ($taksa * $br_mesta / $br_staraoca);

						$podaci[':karton_id'] = $karton->id;
						$podaci[':staraoc_id'] = $staraoc->id;
						$podaci[':tip'] = 'taksa';
						$podaci[':iznos_zaduzeno'] = $iznos_takse;

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

						$podaci[':karton_id'] = $karton->id;
						$podaci[':staraoc_id'] = $staraoc->id;
						$podaci[':tip'] = 'zakup';
						$podaci[':iznos_zaduzeno'] = $iznos_zakupa;

						$stmt->execute($podaci);
					}
				}
			}

			$pdo->commit();

			$this->flash->addMessage('success', 'Svi aktivni kartoni su uspešno zaduženi.');
			return $response->withRedirect($this->router->pathFor('transakcije.zaduzivanje'));
		}
	}


	public function postUplata($request, $response)
	{
		$data = $request->getParams();

		// dd($data);

		$id = $data['staraoc_id'];

		// niz id-a zaduzenja
		$zaduzenja_data = isset($data['razduzeno-zaduzenje']) ? $data['razduzeno-zaduzenje'] : [];
		// niz id-a racuna
		$racuni_data = isset($data['razduzeno-racuni']) ? $data['razduzeno-racuni'] : [];

		if (empty($zaduzenja_data) && empty($racuni_data))
		{
			// nista nije cekirano => vrati gresku
			$this->flash->addMessage('danger', 'Nije čekirana nijedna stavka za razduživanje.');
			return $response->withRedirect($this->router->pathFor('transakcije.razduzivanje', ['id' => $id]));
		}

		unset($data['csrf_name']);
		unset($data['csrf_value']);
		unset($data['razduzeno-zaduzenje']);
		unset($data['razduzeno-racuni']);

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
		];

		$this->validator->validate($data, $validation_rules);

		// provera da li je iznos >= sva zaduzenja za razduzivanje
		$model_zaduzenje = new Zaduzenje();
		$model_racun = new Racun();
		// $model_reprogram = new Reprogram();
		// $cene = new Cena();

		$zaduzenja = null;
		$racuni = null;

		$iznos_za_razduzenje = 0;

		if (!empty($zaduzenja_data))
		{
			$zad = implode(", ", $zaduzenja_data);
			$sql = "SELECT * FROM zaduzenja WHERE id IN ($zad);";
			$zaduzenja = $model_zaduzenje->fetch($sql);

			foreach ($zaduzenja as $zad)
			{
				$iznos_za_razduzenje += $zad->zaRazduzenje();
			}
		}

		if (!empty($racuni_data))
		{
			$rac = implode(", ", $racuni_data);
			$sql = "SELECT * FROM racuni WHERE id IN ($rac);";
			$racuni = $model_racun->fetch($sql);

			foreach ($racuni as $rn)
			{
				$iznos_za_razduzenje += $rn->iznos;
			}
		}

		// iznos za uplatu
		$iznos = (float) $data['uplata_iznos'];
		// razlika uplacenog i potrebnog novca za razduzenje
		$razlika = $iznos - $iznos_za_razduzenje;

		$br_cekiranih_zaduzenja = empty($zaduzenja_data) ? 0 : count($zaduzenja_data);
		$br_cekiranih_racuna = empty($racuni_data) ? 0 : count($racuni_data);

		if ($razlika < 0) // uplaceno je manje novca od potrebnog
		{
			// da li je cekirana samo jedna stavka zaduzenja
			if ($br_cekiranih_zaduzenja != 1 || $br_cekiranih_racuna != 0)
			{
				$this->flash->addMessage('danger', 'Iznos uplate nije dovoljan za razduživanje odabranih stavki.');
				return $response->withRedirect($this->router->pathFor('transakcije.razduzivanje', ['id' => $id]));
			}
		}

		if ($this->validator->hasErrors())
		{
			$this->flash->addMessage('danger', 'Došlo je do greške prilikom snimanja uplate i razduživanja.');
			return $response->withRedirect($this->router->pathFor('transakcije.razduzivanje', ['id' => $id]));
		}
		else
		{
			$staraoc = (new Staraoc())->find($id);
			$korisnik_id = $this->auth->user()->id;

			// podaci za uplatu
			$uplata_data = [
				'karton_id' => $staraoc->karton()->id,
				'staraoc_id' => $staraoc->id,
				'iznos' => $iznos,
				'datum' => $data['uplata_datum'],
				'priznanica' => $data['uplata_priznanica'],
				'napomena' => $data['uplata_napomena'],
				'korisnik_id' => $korisnik_id,
			];

			$model_uplata = new Uplata();
			$model_uplata->insert($uplata_data);
			$uplata_id = $model_uplata->getLastId();

			$sql = "UPDATE staraoci SET privremeni_saldo = privremeni_saldo + {$razlika}, uplata_id = {$uplata_id} WHERE id = {$id};";
			$staraoc->run($sql);

			// proveriti da li je delimicno razduzivanje
			$razduzeno = ($razlika < 0) ? 0 : 1;
			$taksa = $razduzeno === 1 ? $staraoc->taksaZaGodinu() : $iznos;
			$zakup = $razduzeno === 1 ? $staraoc->zakupZaGodinu() : $iznos;

			if (!empty($zaduzenja_data))
			{
				$zad = implode(", ", $zaduzenja_data);

				$sql = "UPDATE zaduzenja
                        SET razduzeno = {$razduzeno}, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id},
                        uplata_id = {$uplata_id}, iznos_razduzeno = {$taksa}
                        WHERE id IN ($zad) AND tip = 'taksa';";
				$model_uplata->run($sql);

				$sql = "UPDATE zaduzenja
                        SET razduzeno = {$razduzeno}, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id},
                        uplata_id = {$uplata_id}, iznos_razduzeno = {$zakup}
                        WHERE id IN ($zad) AND tip = 'zakup';";
				$model_uplata->run($sql);
			}

			if (!empty($racuni_data))
			{
				$rac = implode(", ", $racuni_data);
				$sql = "UPDATE racuni
                        SET razduzeno = 1, datum_razduzenja = CURDATE(), korisnik_id_razduzio = {$korisnik_id},
						uplata_id = {$uplata_id}
                        WHERE id IN ($rac);";
				$model_uplata->run($sql);
			}

			$this->flash->addMessage('success', 'Uplata je uspešno sačuvana, a odabrane stavke su razdužene.');
			return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $id]));
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
		// razduzeno = 1
		// iznos_razduzeno > 0
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
		// $karton_id = (int) $request->getParam('karton_id');

		// $sqlz = "DELETE FROM zaduzenja WHERE karton_id = :kar;";
		// $modelZaduzenja = new Zaduzenje();
		// $successz = $modelZaduzenja->run($sqlz, [':kar' => $karton_id]);

		// $sqlr = "DELETE FROM racuni WHERE karton_id = :kar;";
		// $modelRacuna = new Racun();
		// $successr = $modelRacuna->run($sqlr, [':kar' => $karton_id]);

		// $sqle = "DELETE FROM reprogrami WHERE karton_id = :kar;";
		// $modelReprogram = new Reprogram();
		// $successe = $modelReprogram->run($sqle, [':kar' => $karton_id]);

		// $sqlu = "DELETE FROM uplate WHERE karton_id = :kar;";
		// $modelUplata = new Uplata();
		// $successu = $modelUplata->run($sqlu, [':kar' => $karton_id]);

		// if ($successz || $successr || $successe || $successu)
		// {
		// 	$this->flash->addMessage('success', "Zaduženje, računi i uplate su uspešno obrisane.");
		// 	return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
		// }
		// else
		// {
		// 	$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja zaduženja.");
		// 	return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $karton_id]));
		// }
	}
}
