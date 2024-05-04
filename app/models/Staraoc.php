<?php

namespace App\Models;

use App\Classes\Model;

class Staraoc extends Model
{
	protected $table = 'staraoci';

	// ==========================================================================================================================
	// POMOCNE METODE
	// ==========================================================================================================================

	public function punoIme()
	{
		$si = empty($this->srednje_ime) ? "" : "({$this->srednje_ime}) ";
		return "{$this->prezime} {$si}{$this->ime}";
	}

	public function adresa()
	{
		return "{$this->mesto}, {$this->ulica} {$this->broj}";
	}

	// prikaz chk aktivan na pogledu
	public function aktivan()
	{
		$chk = $this->aktivan === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk}>";
	}

	// prikaz chk aktivan na pogledu (nije moguce promeniti klikom)
	public function aktivanDisabled()
	{
		$chk = $this->aktivan === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk} disabled>";
	}

	// ==========================================================================================================================
	// VEZE ZA DRUGIM TABELAMA (FOREIGN KEYS)
	// ==========================================================================================================================

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function zaduzenja()
	{
		return $this->hasMany('App\Models\Zaduzenje', 'staraoc_id');
	}

	public function racuni()
	{
		$sql = "SELECT * FROM racuni WHERE staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Racun');
	}

	public function reprogrami()
	{
		return $this->hasMany('App\Models\Reprogram', 'staraoc_id');
	}

	// ==========================================================================================================================
	// REPROGRAMI - racunati kao da nije odradjeno zbog promena u ostatku programa
	// ==========================================================================================================================

	public function sviReprogrami()
	{
		$sql = "SELECT * FROM reprogrami WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id} ORDER BY datum DESC;";
		return $this->fetch($sql, null, '\App\Models\Reprogram');
	}

	public function zaduzeniReprogrami()
	{
		$sql = "SELECT * FROM reprogrami WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id}
                AND razduzeno = 0 ORDER BY datum DESC;";
		return $this->fetch($sql, null, '\App\Models\Reprogram');
	}

	public function razduzeniReprogrami()
	{
		$sql = "SELECT * FROM reprogrami WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id}
                AND razduzeno = 1 ORDER BY datum DESC;";
		return $this->fetch($sql, null, '\App\Models\Reprogram');
	}

	public function dugZaReprograme()
	{
		$sql = "SELECT SUM(iznos_rate * preostalo_rata) AS dug FROM reprogrami WHERE razduzeno = 0
                AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		$dug = (float) $this->fetch($sql)[0]->dug;
		return round($dug, 2);
	}

	// ==========================================================================================================================
	// ZADUZENJA
	// ==========================================================================================================================

	// sva zaduzenja staraoca
	public function svaZaduzenja()
	{
		// XXX da li je potrebano sortiranje po godini
		$sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id} ORDER BY godina ASC;";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// sva zaduzenja staraoca koja nisu u potpunosti razduzena
	public function zaduzenaZaduzenja()
	{
		// XXX sortiranje?
		$sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id}
                AND razduzeno = 0 AND reprogram_id IS NULL ORDER BY godina ASC, tip ASC;";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// sva zaduzenja staraoca koja su u potpunosti razduzena
	public function razduzenaZaduzenja()
	{
		// XXX sortiranje?
		$sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id}
                AND razduzeno = 1 ORDER BY godina ASC;";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// TAKSE

	// odredjuje iznos takse za godinu (zaduzenje staraoca za godinu)
	public function taksaZaGodinu()
	{
		// BUG uzima se poslednja cena iz cenovnika
		// potrebno je da se koristi uneta cene prilikom zaduzivanja
		$cena = (float) (new Cena())->taksa();
		$br_mesta = $this->karton()->broj_mesta;
		$br_staraoca = $this->karton()->brojAktivnihStaraoca();
		$taksa = $cena * $br_mesta / $br_staraoca;
		return (float) $taksa;
	}

	// sve takse staraoca
	public function sveTakse()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// sve takse staraoca koje nisu u potpunosti razduzene
	public function zaduzeneTakse()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// sve takse staraoca koje su u potpunosti razduzene
	public function razduzeneTakse()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 1
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// ukupan dug staraoca za takse
	public function dugZaTakse()
	{
		// TODO izbaciti zateznu kamatu
		$takse = $this->zaduzeneTakse();
		$rez = 0;

		foreach ($takse as $taksa)
		{
			$rez += $taksa->zaRazduzenje()['ukupno'];
		}

		return round($rez, 2);
	}

	// XXX takse zaduzene posle tekuce godine
	// postoji zaduzivanja u buducnost ali mislim da ovo nije potrebno
	// ovo ne treba da postoji jer nema vise zaduzivanja u buducnost
	// public function taksePosleTekuceGodine()
	// {
	// 	$god = GOD;
	// 	$sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0
	//             AND godina > {$god} AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
	// 	$broj = (int) $this->fetch($sql)[0]->broj;
	// 	return $broj;
	// }


	// ZAKUPI

	// odredjuje iznos zakupa za godinu (zaduzenje staraoca za godinu)
	public function zakupZaGodinu()
	{
		// BUG uzima se poslednja cena iz cenovnika
		// potrebno je da se koristi uneta cene prilikom zaduzivanja
		$cena = (float) (new Cena())->zakup();
		$br_mesta = $this->karton()->broj_mesta;
		$br_staraoca = $this->karton()->brojAktivnihStaraoca();
		$zakup = $cena * $br_mesta / $br_staraoca;
		return (float) $zakup;
	}

	// svi zakupi staraoca
	public function sviZakupi()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// svi zakupi staraoca koji nisu u potpunosti razduzeni
	public function zaduzeniZakupi()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// svi zakupi staraoca koji su u potpunosti razduzeni
	public function razduzeniZakupi()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 1
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// XXX ovo nije potrebno
	// public function saldoZaZakupe()
	// {
	//     $sql = "SELECT SUM(iznos_razduzeno) AS saldo FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0 AND iznos_razduzeno > 0
	//             AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
	//     $saldo = $this->fetch($sql)[0]->saldo;
	//     return round((float) $saldo, 2);
	// }

	// ukupan dug staraoca za zakupe
	public function dugZaZakupe()
	{
		// TODO izbaciti zateznu kamatu
		$zakupi = $this->zaduzeniZakupi();
		$rez = 0;

		foreach ($zakupi as $zakup)
		{
			$rez += $zakup->zaRazduzenje()['ukupno'];
		}

		return round($rez, 2);
	}

	// XXX ovo ne treba da postoji jer nema vise zaduzivanja u buducnost
	// postoji zaduzivanja u buducnost ali mislim da ovo nije potrebno
	// public function zakupiPosleTekuceGodine()
	// {
	// 	$god = GOD;
	// 	$sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0
	//             AND godina > {$god} AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
	// 	$broj = (int) $this->fetch($sql)[0]->broj;
	// 	return $broj;
	// }

	// RACUNI

	// svi racuni staraoca
	public function sviRacuni()
	{
		$sql = "SELECT * FROM racuni WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Racun');
	}

	// svi racuni staraoca koji nisu u potpunosti razduzeni
	public function zaduzeniRacuni()
	{
		$sql = "SELECT * FROM racuni WHERE razduzeno = 0 AND reprogram_id IS NULL
                AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Racun');
	}

	// svi racuni staraoca koji su u potpunosti razduzeni
	public function razduzeniRacuni()
	{
		$sql = "SELECT * FROM racuni WHERE razduzeno = 1 AND reprogram_id IS NULL
                AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Racun');
	}

	// ukupan dug staraoca za racune
	public function dugZaRacune()
	{
		// TODO izbaciti zateznu kamatu
		$racuni = $this->zaduzeniRacuni();
		$rez = 0;

		foreach ($racuni as $rn)
		{
			$rez += $rn->zaRazduzenje()['ukupno'];
		}

		return round($rez, 2);
	}

	// dug staraoca za takse, zakupe i racune
	public function ukupanDug()
	{
		return $this->dugZaTakse() + $this->dugZaZakupe() + $this->dugZaRacune();
	}

	// // dug staraoca za takse, zakupe, racune i reprograme
	public function ukupanDugSaReprogramima()
	{
		return $this->ukupanDug() + $this->dugZaReprograme();
	}

	// da li staraoc ima upisan neki avans
	public function imaAvans()
	{
		return ((float) $this->avans) > 0 ? true : false;
	}

	// da li staraoc ima avans i nerazduzena zaduzenja (takse, zakupe i racune)
	public function imaAvansNerazduzen()
	{
		$ima_avans = $this->imaAvans();
		$ima_dug = $this->ukupanDug() > 0 ? true : false;

		return $ima_avans && $ima_dug;
	}

	// svi staraoci koji koji imaju avans i nerazduzena zaduzenja (takse, zakupe i racune)
	public function sviSaraociSaNerazduzenimAvansom()
	{
		$sql = "SELECT * FROM staraoci WHERE avans > 0;";
		$staraoci = $this->fetch($sql);
		$rez = [];

		foreach ($staraoci as $staraoc)
		{
			if ($staraoc->ukupanDug() > 0)
			{
				$rez[] = $staraoc;
			}
		}

		return $rez;
	}

	// =========================================================================================================================
	// UPLATE
	// =========================================================================================================================

	// sve uplate staraoca
	public function uplate()
	{
		$sql = "SELECT * FROM uplate WHERE staraoc_id = {$this->id} ORDER BY datum DESC, id DESC;";
		return $this->fetch($sql, null, 'App\Models\Uplata');
	}

	// poslednja uplata staraoca
	public function poslednjaUplata()
	{
		$sql = "SELECT * FROM uplate WHERE staraoc_id = {$this->id} ORDER BY datum DESC, id DESC LIMIT 1;";
		// XXX kada nema nijednnu uplatu vraca prazan niz [] (count = 0)
		return $this->fetch($sql, null, 'App\Models\Uplata');
	}

	// ukupan iznos svih uplata staraoca
	public function ukupanIznosUplata()
	{
		// visak = 1 su fiktivne uplate za razduzivanje viska prave uplate
		// FIXME ovaj visak izbaciti
		$sql = "SELECT SUM(iznos) AS iznos_uplata FROM uplate WHERE staraoc_id = {$this->id} AND visak = 0;";
		$iznos = (float) $this->fetch($sql)[0]->iznos_uplata;

		return $iznos;
	}

	// ovo je pravi avans
	public function avans()
	{
		// zbir svih pravih avansa iz uplata staraoca
		$sql = "SELECT SUM(avans) AS ukupno FROM uplate WHERE staraoc_id = {$this->id}";
		$avans = (float)(new Uplata)->fetch($sql)[0]->ukupno;
		return $avans;
	}

	// sve uplate sa avansom
	public function uplateSaAvansom()
	{
		$sql = "SELECT * FROM uplate WHERE staraoc_id = {$this->id} AND avans > 0 ORDER BY datum ASC;";
		return (new Uplata)->fetch($sql);
	}

	// Brisanje svih zaduzenja i uplata staraoca
	public function brisanjeZaduzenjaIUplata()
	{
		$id = $this->id;
		$sql1 = "DELETE FROM zaduzenje_uplata WHERE staraoc_id = :id;";
		$sql2 = "DELETE FROM zaduzenja WHERE staraoc_id = :id;";
		$sql3 = "DELETE FROM uplate WHERE staraoc_id = :id;";

		$succ1 = $this->run($sql1, [":id" => $id]);
		$succ2 = $this->run($sql2, [":id" => $id]);
		$succ3 = $this->run($sql3, [":id" => $id]);

		return $succ1 && $succ2 && $succ3;
	}
}
