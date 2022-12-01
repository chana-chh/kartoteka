<?php

namespace App\Models;

use App\Classes\Model;

class Staraoc extends Model
{
	protected $table = 'staraoci';

	public function punoIme()
	{
		$si = empty($this->srednje_ime) ? "" : "({$this->srednje_ime}) ";
		return "{$this->prezime} {$si}{$this->ime}";
	}

	public function adresa()
	{
		return "{$this->mesto}, {$this->ulica} {$this->broj}";
	}

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function uplate()
	{
		$sql = "SELECT * FROM uplate WHERE staraoc_id = {$this->id} ORDER BY datum DESC;";
		return $this->fetch($sql, null, 'App\Models\Uplata');
	}

	public function poslednjaUplata()
	{
		$sql = "SELECT * FROM uplate WHERE staraoc_id = {$this->id} ORDER BY datum DESC LIMIT 1;";
		return $this->fetch($sql, null, 'App\Models\Uplata')[0];
	}

	public function ukupanIznosUplata()
	{
		// visak = 1 su fiktivne uplate za razduzivanje viska prave uplate
		$sql = "SELECT SUM(iznos) AS iznos_uplata FROM uplate WHERE staraoc_id = {$this->id} AND visak = 0;";
		$iznos = (float) $this->fetch($sql)[0]->iznos_uplata;

		return $iznos;
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

	// jedan prema vise na zaduzenje
	public function zaduzenja()
	{
		return $this->hasMany('App\Models\Zaduzenje', 'staraoc_id');
	}

	// jedan prema vise na reprograme
	public function reprogrami()
	{
		return $this->hasMany('App\Models\Reprogram', 'staraoc_id');
	}

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



	// new ****************************************************************************************************************************
	// novi nacin razduzivanja uz zateznu kamatu



	public function svaZaduzenja()
	{
		$sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id} ORDER BY godina ASC;";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	public function zaduzenaZaduzenja()
	{
		$sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id}
                AND razduzeno = 0 AND reprogram_id IS NULL ORDER BY godina ASC, tip ASC;";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	public function razduzenaZaduzenja()
	{
		$sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id}
                AND razduzeno = 1 ORDER BY godina ASC;";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// takse

	public function taksaZaGodinu()
	{
		$cena = (float) (new Cena())->taksa();
		$br_mesta = $this->karton()->broj_mesta;
		$br_staraoca = $this->karton()->brojAktivnihStaraoca();
		$taksa = $cena * $br_mesta / $br_staraoca;
		return (float) $taksa;
	}

	public function sveTakse()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	public function zaduzeneTakse()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	public function razduzeneTakse()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 1
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	public function dugZaTakse()
	{
		$takse = $this->zaduzeneTakse();
		$rez = 0;

		foreach ($takse as $taksa)
		{
			$rez += $taksa->zaRazduzenje()['ukupno'];
		}

		return round($rez, 2);
	}

	// takse zaduzene posle tekuce godine
	// ovo ne treba da postoji jer nema vise zaduzivanja u buducnost
	// public function taksePosleTekuceGodine()
	// {
	// 	$god = GOD;
	// 	$sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0
    //             AND godina > {$god} AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
	// 	$broj = (int) $this->fetch($sql)[0]->broj;
	// 	return $broj;
	// }

	
	// zakupi

	public function zakupZaGodinu()
	{
		$cena = (float) (new Cena())->zakup();
		$br_mesta = $this->karton()->broj_mesta;
		$br_staraoca = $this->karton()->brojAktivnihStaraoca();
		$zakup = $cena * $br_mesta / $br_staraoca;
		return (float) $zakup;
	}

	public function sviZakupi()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	public function zaduzeniZakupi()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	public function razduzeniZakupi()
	{
		$sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 1
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Zaduzenje');
	}

	// public function saldoZaZakupe()
	// {
	//     $sql = "SELECT SUM(iznos_razduzeno) AS saldo FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0 AND iznos_razduzeno > 0
	//             AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
	//     $saldo = $this->fetch($sql)[0]->saldo;
	//     return round((float) $saldo, 2);
	// }

	public function dugZaZakupe()
	{
		$zakupi = $this->zaduzeniZakupi();
		$rez = 0;

		foreach ($zakupi as $zakup)
		{
			$rez += $zakup->zaRazduzenje()['ukupno'];
		}

		return round($rez, 2);
	}

	// ovo ne treba da postoji jer nema vise zaduzivanja u buducnost
	// public function zakupiPosleTekuceGodine()
	// {
	// 	$god = GOD;
	// 	$sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0
    //             AND godina > {$god} AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
	// 	$broj = (int) $this->fetch($sql)[0]->broj;
	// 	return $broj;
	// }

	public function racuni()
	{
		$sql = "SELECT * FROM racuni WHERE staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Racun');
	}

	public function sviRacuni()
	{
		$sql = "SELECT * FROM racuni WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Racun');
	}

	public function zaduzeniRacuni()
	{
		$sql = "SELECT * FROM racuni WHERE razduzeno = 0 AND reprogram_id IS NULL
                AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Racun');
	}

	public function razduzeniRacuni()
	{
		$sql = "SELECT * FROM racuni WHERE razduzeno = 1 AND reprogram_id IS NULL
                AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
		return $this->fetch($sql, null, '\App\Models\Racun');
	}

	public function dugZaRacune()
	{
		$racuni = $this->zaduzeniRacuni();
		$rez = 0;

		foreach ($racuni as $rn)
		{
			$rez += $rn->zaRazduzenje()['ukupno'];
		}

		return round($rez, 2);
	}

	public function ukupanDug()
	{
		return $this->dugZaTakse() + $this->dugZaZakupe() + $this->dugZaRacune();
	}

	public function ukupanDugSaReprogramima()
	{
		return $this->dugZaTakse() + $this->dugZaZakupe() + $this->dugZaRacune() + $this->dugZaReprograme();
	}

	public function imaAvans()
	{
		return ((float) $this->avans) > 0 ? true : false;
	}

	public function imaAvansNerazduzen()
	{
		$ima_avans = $this->imaAvans();
		$ima_dug = $this->ukupanDug() > 0 ? true : false;

		return $ima_avans && $ima_dug;
	}

	public function sviSaraociSaNerazduzenimAvansom()
	{
		$sql = "SELECT * FROM staraoci WHERE avans > 0;";
		$staraoci = $this->fetch($sql);
		$rez=[];

		foreach ($staraoci as $staraoc)
		{
			if ($staraoc->ukupanDug() > 0)
			{
				$rez[] = $staraoc;
			}
		}

		return $rez;
	}
}
