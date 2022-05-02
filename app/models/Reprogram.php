<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Reprogram extends Model
{
	protected $table = 'reprogrami';

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function staraoc()
    {
        return $this->belongsTo('App\Models\Staraoc', 'staraoc_id');
    }

	public function zaduzenja()
	{
		return $this->hasMany('App\Models\Zaduzenje', 'reprogram_id');
	}

	public function zakupi()
	{
		$sql = "SELECT * FROM zaduzenja WHERE reprogram_id = {$this->id} AND tip = 'zakup';";
		return $this->fetch($sql, null, 'App\Models\Zaduzenje');
	}

	public function takse()
	{
		$sql = "SELECT * FROM zaduzenja WHERE reprogram_id = {$this->id} AND tip = 'taksa';";
		return $this->fetch($sql, null, 'App\Models\Zaduzenje');
	}

	public function racuni()
	{
		return $this->hasMany('App\Models\Racun', 'reprogram_id');
	}

	public function dug()
	{
		return round(($this->iznos / $this->period * $this->preostalo_rata), 2);
	}

	public function rata()
	{
		return round($this->iznos / $this->period, 2);
	}

	public function razduzeno()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-reprogrami[]\" value=\"{$this->$pk}\" data-iznos=\"{$this->dug()}\" class=\"razduzeno-reprogrami\"{$chk}>";
	}

	public function razduzenoDisabled()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-reprogrami[]\" value=\"{$this->$pk}\" data-iznos=\"{$this->iznos}\" class=\"razduzeno-reprogrami\"{$chk} disabled>";
	}

	public function datum()
	{
		if ($this->datum === null) {
			return "";
		} else {
			return DateTime::createFromFormat('Y-m-d', $this->datum)->format('d.m.Y');
		}
	}

	public function datum_razduzenja()
	{
		if ($this->datum_razduzenja === null) {
			return "";
		} else {
			return DateTime::createFromFormat('Y-m-d', $this->datum)->format('d.m.Y');
		}
	}

	public function rate()
	{
		$rate = [];
		$iznos = $this->iznos_rate;
		$prva_rata = $this->datum_prve_rate;
		$br_rata = $this->period;
		$preostalo = $this->preostalo_rata;
		$isplaceno = $br_rata - $preostalo;

		for ($i=1; $i <= $br_rata; $i++)
		{ 
			$rate[$i] = [
				'datum' => $prva_rata,
				'iznos' => round($iznos, 2),
				'isplacena' => ($i <= $isplaceno) ? true : false,
			];

			$prva_rata = date('Y-m-d', strtotime("+1 month", strtotime($prva_rata)));
		}

		return $rate;
	}

	public function korisnikZaduzio()
    {
		$korisnik = (new Korisnik())->find((int) $this->korisnik_id_zaduzio);
        return $korisnik;
    }
	
	public function korisnikRazduzio()
    {
		$korisnik = (new Korisnik())->find((int) $this->korisnik_id_razduzio);
        return $korisnik;
    }

	public function uplate()
	{
		$sql = "SELECT * FROM uplate WHERE reprogram_id = {$this->id};";
		return $this->fetch($sql, null, 'App\Models\Uplata');
	}
}
