<?php

namespace App\Models;

use App\Classes\Model;
use App\Models\Korisnik;
use App\Models\Uplata;

class Zaduzenje extends Model
{
	protected $table = 'zaduzenja';

	public function reprogram()
	{
		return $this->belongsTo('App\Models\Reprogram', 'reprogram_id');
	}

	public function reprogramChk($reprogram_id)
	{
		$chk = ($this->reprogram_id != null && $this->reprogram_id === $reprogram_id) ? ' checked' : '';
		return "<input type=\"checkbox\" name=\"reprogram-zaduzenje[]\" value=\"{$this->id}\" data-tip=\"{$this->tip}\" class=\"reprogram-zaduzenja\"{$chk}>";
	}

	public function reprogramLbl($reprogram_id)
	{
		$lbl = $this->reprogram_id === $reprogram_id ? "{$this->reprogram()->broj}" : '';
		return $lbl;
	}

	public function razduzeno()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-zaduzenje[]\" value=\"{$this->$pk}\" data-tip=\"{$this->tip}\" data-iznos=\"{$this->zaRazduzenje()}\" class=\"razduzeno-zaduzenja\"{$chk}>";
	}

	public function razduzenoDisabled()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-zaduzenje[]\" value=\"{$this->$pk}\" data-tip=\"{$this->tip}\" class=\"razduzeno-zaduzenja\"{$chk} disabled>";
	}

	// vise prema jedan na staraoca
	public function staraoc()
    {
        return $this->belongsTo('App\Models\Staraoc', 'staraoc_id');
    }
	
	public function karton()
    {
        return $this->belongsTo('App\Models\Karton', 'karton_id');
    }

	public function zaRazduzenje()
	{
		if($this->razduzeno === 1)
		{
			return 0;
		}

		$cene = new Cena();
		$cena = $cene->taksa();

		if ($this->tip === 'zakup')
		{
			$cena = $cene->zakup();
		}

		$obracunato = (float) ($cena * $this->karton()->broj_mesta / $this->karton()->brojAktivnihStaraoca());

		return round($obracunato - $this->iznos_razduzeno, 2);
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
	
	public function uplata()
    {
        $uplata = (new Uplata())->find((int) $this->uplata_id);
        return $uplata;
    }
}
