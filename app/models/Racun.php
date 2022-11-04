<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Racun extends Model
{
	protected $table = 'racuni';

	// public function artikli()
	// {
	// 	$this->belongsToMany('App\Models\Artikal', 'racun_artikal', 'racun_id', 'artikal_id');
	// }

	public function reprogram()
	{
		return $this->belongsTo('App\Models\Reprogram', 'reprogram_id');
	}

	public function reprogramChk($reprogram_id)
	{
		$chk = ($this->reprogram_id != null && $this->reprogram_id === $reprogram_id) ? ' checked' : '';
		return "<input type=\"checkbox\" name=\"reprogram-racuni[]\" value=\"{$this->id}\" data-iznos=\"{$this->iznos}\" class=\"reprogram-racuni\"{$chk}>";
	}

	public function reprogramLbl($reprogram_id)
	{
		$lbl = $this->reprogram_id === $reprogram_id ? "{$this->reprogram()->broj}" : '';
		return $lbl;
	}

	public function reprogramCheckDisabled()
	{
		return null;
	}

	public function razduzeno()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-racuni[]\" value=\"{$this->$pk}\" data-iznos=\"{$this->iznos}\" class=\"razduzeno-racuni\"{$chk}>";
	}

	public function razduzenoDisabled()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-racuni[]\" value=\"{$this->$pk}\" data-iznos=\"{$this->iznos}\" class=\"razduzeno-racuni\"{$chk} disabled>";
	}

	public function datum()
	{
		if ($this->datum === null) {
			return "";
		} else {
			return DateTime::createFromFormat('Y-m-d', $this->datum)->format('d.m.Y');
		}
	}

	public function rok()
	{
		$sql = "SELECT * FROM {$this->table} WHERE razduzeno = 0 AND datum_prispeca IS NOT NULL AND datum_prispeca >= CURDATE() AND datum_prispeca < DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    	return $this->fetch($sql);
	}

	public function istekli()
	{
		$sql = "SELECT * FROM {$this->table} WHERE razduzeno = 0 AND datum_prispeca IS NOT NULL AND datum_prispeca < CURDATE()";
    	return $this->fetch($sql);
	}

	public function karton()
    {
        $sql = "SELECT * FROM kartoni WHERE id = {$this->karton_id};";
        return $this->fetch($sql, null, '\App\Models\Karton')[0];
    }

	public function staraoc()
    {
        $sql = "SELECT * FROM staraoci WHERE id = {$this->staraoc_id};";
        return $this->fetch($sql, null, '\App\Models\Staraoc')[0];
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

    public function dugRacun()
    {
    	$sql = "SELECT SUM(iznos) AS ukupno
				FROM {$this->table}
				WHERE razduzeno = 0 AND reprogram_id IS NULL;";
		$broj = $this->fetch($sql)[0]->ukupno;
		return (float) $broj;
    }

	public function zaRazduzenje()
	{
		$datum_prispeca = date($this->datum_prispeca);
		$glavnica = (float) $this->glavnica;
		$trenutni_datum = date('Y-m-d');
		$zatezna = 0;

		if ($this->razduzeno === 1 || $this->reprogram_id !== null)
		{
			return [
				'glavnica' => $glavnica,
				'kamata' =>	$zatezna,
				'ukupno' => $glavnica + $zatezna,
			];
		}

		if ($this->datum_prispeca === null)
		{
			return [
				'glavnica' => $glavnica,
				'kamata' =>	$zatezna,
				'ukupno' => $glavnica + $zatezna,
			];
		}

		$kamate = (new Kamata())->kamateZaObracun($datum_prispeca, $trenutni_datum);

		if (empty($kamate))
		{
			return [
				'glavnica' => $glavnica,
				'kamata' =>	$zatezna,
				'ukupno' => $glavnica + $zatezna,
			];
		}

		foreach ($kamate as $k => $v)
		{
			$kam = ($glavnica * $v['procenat'] * $v['dana']) / (100 * $v['godina']);
			$zatezna += $kam;
			$kamate[$k]['iznos'] = $kam;
		}

		return [
			'glavnica' => $glavnica,
			'kamata' =>	$zatezna,
			'ukupno' => $glavnica + $zatezna,
		];
	}
}
