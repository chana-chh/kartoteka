<?php

namespace App\Controllers;

use App\Models\Staraoc;
use App\Models\Uplata;
use App\Models\Log;
use App\Models\Racun;
use App\Models\Zaduzenje;

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
		dd('!!! BRISANJE UPLATE TRENUTNO NIJE AKTIVNO !!!');

		// brise se samo poslednja uplata
		// sta je sa avansom kad se brise uplata ???
		// onemoguciti brisanje uplate kada postoji nerazduzeni avans ili
		// vratiti avans staraocu ???

		$id = (int) $request->getParam('modal_uplata_id');
		$uplata = (new Uplata())->find($id);
		$staraoc_id = $uplata->staraoc()->id;
		$staraoc = (new Staraoc())->find($staraoc_id);

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
			$sql = "UPDATE zaduzenja SET razduzeno = 0, iznos_razduzeno = 0, korisnik_id_razduzio = NULL, uplata_id = NULL,
							glavnica = poslednja_glavnica, datum_prispeca = poslednji_datum_prispeca, datum_razduzenja = NULL,
							poslednja_glavnica = 0, poslednji_datum_prispeca = NULL,
							iznos_razduzeno = iznos_zaduzeno - poslednja_glavnica
					WHERE uplata_id = {$uplata->id}";
			$uplata->run($sql);

			$sql = "UPDATE racuni SET razduzeno = 0, korisnik_id_razduzio = NULL, uplata_id = NULL,
							glavnica = poslednja_glavnica, datum_prispeca = poslednji_datum_prispeca, datum_razduzenja = NULL,
							poslednja_glavnica = 0, poslednji_datum_prispeca = NULL,
							iznos_razduzeno = iznos - poslednja_glavnica
					WHERE uplata_id = {$uplata->id}";
			$uplata->run($sql);

			// ako je $uplata->visak == 0 $uplata->avans oduzeti od $staraoc->avans
			// ako je $uplata->visak == 1 $uplata->avans dodati na $staraoc->avans
			if ($uplata->visak == 0)
			{
				$staraoc->update(['avans' => $staraoc->avans - $uplata->iznos], $staraoc_id);
			}
			else
			{
				$staraoc->update(['avans' => $staraoc->avans + $uplata->iznos], $staraoc_id);
			}
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
						'datum_prispeca' => date('Y-m-d'),
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
							'datum_prispeca' => date('Y-m-d'),
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

	public function postVisakUnos($request, $response)
	{
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);
		dd($data);
		// proveriti da li je odabrano samo jedno zaduzenje
		// proveriti da li je visak manji od glavnice odabranog zaduzenja
		// delimicno razduziti odabrana zaduzenje
	}
}
