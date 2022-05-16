<?php

namespace App\Controllers;

use App\Models\Staraoc;
use App\Models\Uplata;
use App\Models\Log;

class UplataController extends Controller
{

	public function getUplate($request, $response, array $args)
	{
		$id = (int) $args['id'];
		$staraoc = (new Staraoc())->find($id);

		$this->render($response, 'uplate.twig', compact('staraoc'));
	}

	public function postUplataBrisanje($request, $response)
	{
		$id = (int) $request->getParam('modal_uplata_id');
		$uplata = (new Uplata())->find($id);
		$staraoc_id = $uplata->staraoc()->id;

		if ($this->auth->user()->nivo !== 0)
		{
			$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja uplate.");
			return $response->withRedirect($this->router->pathFor('uplate', ['id' => $staraoc_id]));
		}

		if ($uplata->reprogram_id !== null)
		{
			$sql = "UPDATE reprogrami SET preostalo_rata = preostalo_rata + {$uplata->broj_rata}, korisnik_id_razduzio = NULL
					WHERE id = {$uplata->reprogram_id};";
			$uplata->run($sql);
		}
		else
		{
			$sql = "UPDATE zaduzenja SET razduzeno = 0, iznos_razduzeno = 0, korisnik_id_razduzio = NULL, uplata_id = NULL
					WHERE uplata_id = {$uplata->id}";
			$uplata->run($sql);

			$sql = "UPDATE racuni SET razduzeno = 0, korisnik_id_razduzio = NULL, uplata_id = NULL
					WHERE uplata_id = {$uplata->id}";
			$uplata->run($sql);
		}
		
		$success = $uplata->deleteOne($id);

		if ($success)
		{
			$this->log($this::BRISANJE, $uplata, ['karton_id', 'datum', 'iznos', 'priznanica'], $uplata);
			$this->flash->addMessage('success', "Uplata je uspešno obrisana.");
			return $response->withRedirect($this->router->pathFor('uplate', ['id' => $staraoc_id]));
		}
		else
		{
			$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja uplate.");
			return $response->withRedirect($this->router->pathFor('uplate', ['id' => $staraoc_id]));
		}
	}

	public function postVisak($request, $response)
	{
		$id = (int) $request->getParam('staraoc_id');
		$staraoc = (new Staraoc())->find($id);

		// sva nerazduzena zaduzenja
		$zaduzenja = $staraoc->zaduzenaZaduzenja();

		// iznos viska para
		$iznos = (float) $staraoc->privremeni_saldo;
		// ostatak viska
		$ostatak = $iznos;

		// ovde ide novi nacin razduzivanja viska para

		// fiksno
		$karton_id = $staraoc->karton()->id;
		$staraoc_id = $staraoc->id;
		$taksa = $staraoc->taksaZaGodinu();
		$zakup = $staraoc->zakupZaGodinu();
		$datum = date('Y-m-d');
		$korisnik_id = $this->auth->user()->id;
		$napomena = 'automatsko razduživanje viška uplate';
		$uplata_id = $staraoc->uplata_id;

		$za_stare = []; // UPDATE
		$za_nove = []; // INSERT

		// prave se podaci za upis

		// taksa ili zakup koji moze delimicno da se razduzi
		$deo = 0;
		// vrsta (taksa ili zakup) za delimicno razduzivanje
		// $vrsta = '';
		// godina takse ili zakupa za delimicno razduzivanje
		// $godina = 0;

		// podaci za stare
		foreach ($zaduzenja as $zad)
		{
			$iznos = $ostatak;
			$razlika = (float) $ostatak - $zad->zaRazduzenje();

			if ($razlika < 0)
			{
				$deo = $iznos;
				$ostatak = 0;
				$za_stare[] = [
					':id' => $zad->id,
					':iznos_razduzeno' => $deo,
					':razduzeno' => 0,
					':datum_razduzenja' => null,
					':korisnik_id_razduzio' => null,
					':napomena' => $napomena,
					':uplata_id' => $uplata_id,
				];
				break;
			}
			else
			{
				$taksa_zakup = ($zad->tip === 'taksa') ? $taksa : $zakup;
				$za_stare[] = [
					':id' => $zad->id,
					':iznos_razduzeno' => $taksa_zakup,
					':razduzeno' => 1,
					':datum_razduzenja' => $datum,
					':korisnik_id_razduzio' => $korisnik_id,
					':napomena' => $napomena,
					':uplata_id' => $uplata_id,
				];

				$ostatak = $razlika;
			}
		}

		// podaci za nove
		if ($ostatak > 0)
		{
			// taksa ili zakup koji moze delimicno da se razduzi
			$n_deo = 0;
			// vrsta (taksa ili zakup) za delimicno razduzivanje
			$n_vrsta = '';
			// godina takse ili zakupa za delimicno razduzivanje
			$n_godina = 0;

			// pocetna godina za nove takse
			$sql = "SELECT MAX(godina) AS max_godina FROM zaduzenja WHERE tip = 'taksa' AND staraoc_id = {$staraoc->id};";
			$godina_za_taksu = (int) $staraoc->fetch($sql)[0]->max_godina + 1;
			$godina_za_taksu = $godina_za_taksu === 1 ? $godina_za_taksu : GOD;
			// pocetna godina za nove zakupe
			$sql = "SELECT MAX(godina) AS max_godina FROM zaduzenja WHERE tip = 'zakup' AND staraoc_id = {$staraoc->id};";
			$godina_za_zakup = (int) $staraoc->fetch($sql)[0]->max_godina + 1;
			$godina_za_zakup = $godina_za_zakup === 1 ? $godina_za_zakup : GOD;

			// odrediti manji pocetnu godinu i krenuti odatle (taksa ili zakup)
			$radi_taksu = $godina_za_taksu <= $godina_za_zakup ? true : false;

			// petlja dok ima ostatka
			do
			{
				$iznos = $ostatak;

				if ($radi_taksu)
				{
					$razlika = (float) $ostatak - $taksa;
				}
				else
				{
					$razlika = (float) $ostatak - $zakup;
				}


				if ($razlika < 0)
				{
					$n_deo = $iznos;

					if ($radi_taksu)
					{
						$n_vrsta = 'taksa';
						$n_godina = $godina_za_taksu;
						$taksa_zakup = $taksa;
					}
					else
					{
						$n_vrsta = 'zakup';
						$n_godina = $godina_za_zakup;
						$taksa_zakup = $zakup;
					}

					$za_nove[] = [
						':karton_id' => $karton_id,
						':staraoc_id' => $staraoc_id,
						':tip' => $n_vrsta,
						':godina' => $n_godina,
						':iznos_zaduzeno' => $taksa_zakup,
						':iznos_razduzeno' => $n_deo,
						':razduzeno' => 0,
						':datum_zaduzenja' => $datum,
						':datum_razduzenja' => null,
						':korisnik_id_zaduzio' => $korisnik_id,
						':korisnik_id_razduzio' => null,
						':reprogram_id' => null,
						':napomena' => $napomena,
						':uplata_id' => $uplata_id,
					];

					$ostatak = 0;
				}
				else
				{
					if ($radi_taksu)
					{
						$za_nove[] = [
							':karton_id' => $karton_id,
							':staraoc_id' => $staraoc_id,
							':tip' => 'taksa',
							':godina' => $godina_za_taksu,
							':iznos_zaduzeno' => $taksa,
							':iznos_razduzeno' => $taksa,
							':razduzeno' => 1,
							':datum_zaduzenja' => $datum,
							':datum_razduzenja' => $datum,
							':korisnik_id_zaduzio' => $korisnik_id,
							':korisnik_id_razduzio' => $korisnik_id,
							':reprogram_id' => null,
							':napomena' => $napomena,
							':uplata_id' => $uplata_id,
						];
						$ostatak = $razlika;
						$godina_za_taksu++;
					}
					else
					{
						$za_nove[] = [
							':karton_id' => $karton_id,
							':staraoc_id' => $staraoc_id,
							':tip' => 'zakup',
							':godina' => $godina_za_zakup,
							':iznos_zaduzeno' => $zakup,
							':iznos_razduzeno' => $zakup,
							':razduzeno' => 1,
							':datum_zaduzenja' => $datum,
							':datum_razduzenja' => $datum,
							':korisnik_id_zaduzio' => $korisnik_id,
							':korisnik_id_razduzio' => $korisnik_id,
							':reprogram_id' => null,
							':napomena' => $napomena,
							':uplata_id' => $uplata_id,
						];
						$ostatak = $razlika;
						$godina_za_zakup++;
					}
				}
				$radi_taksu = $godina_za_taksu <= $godina_za_zakup ? true : false;
			} while ($ostatak > 0);
		}

		$pdo = $staraoc->getDb()->getPDO();

		// strai UPDATE
		$sql_s = "UPDATE `zaduzenja` SET
                iznos_razduzeno = :iznos_razduzeno,
                razduzeno = :razduzeno,
                datum_razduzenja = :datum_razduzenja,
                korisnik_id_razduzio = :korisnik_id_razduzio,
                napomena = :napomena,
                uplata_id = :uplata_id
                WHERE id = :id;";

		$stmt = $pdo->prepare($sql_s);
		$pdo->beginTransaction();

		foreach ($za_stare as $zaduzenje)
		{
			$stmt->execute($zaduzenje);
		}

		$pdo->commit();

		// novi INSERT
		$sql_n = "INSERT INTO `zaduzenja`
                (karton_id,
                staraoc_id,
                tip,
                godina,
                iznos_zaduzeno,
                iznos_razduzeno,
                razduzeno,
                datum_zaduzenja,
                datum_razduzenja,
                korisnik_id_zaduzio,
                korisnik_id_razduzio,
                reprogram_id,
                napomena,
                uplata_id)
                VALUES
                (:karton_id,
                :staraoc_id,
                :tip,
                :godina,
                :iznos_zaduzeno,
                :iznos_razduzeno,
                :razduzeno,
                :datum_zaduzenja,
                :datum_razduzenja,
                :korisnik_id_zaduzio,
                :korisnik_id_razduzio,
                :reprogram_id,
                :napomena,
                :uplata_id);";

		$stmt = $pdo->prepare($sql_n);
		$pdo->beginTransaction();

		foreach ($za_nove as $zaduzenje)
		{
			$stmt->execute($zaduzenje);
		}

		$pdo->commit();

		// posle upisa staraoc->privremeni_saldo se vraca na 0 i id uplate na null
		$sql_s = "UPDATE staraoci SET privremeni_saldo = 0, uplata_id = NULL WHERE id = {$staraoc_id}";
		$staraoc->run($sql_s);
			$modelLoga = new Log();
            $datal['tip'] = "dodavanje";
            $datal['opis'] = "Potvrđena preraspodela preostalih sredstava za razduživanje taksi i zakupa. Karton: " .$karton_id. ", staraoc: " .$staraoc_id. ", datum: " .$datum;
            $datal['korisnik_id'] = $this->auth->user()->id;
            $modelLoga->insert($datal);
		$this->flash->addMessage('success', 'Višak novca je uspešno raspoređen na razduživanje taksi i zakupa.');
		return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $id]));
	}
}
