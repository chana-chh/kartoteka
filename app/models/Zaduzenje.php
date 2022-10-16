<?php

namespace App\Models;

use App\Classes\Model;
use App\Models\Korisnik;
use App\Models\Uplata;
use App\Models\Kamata;

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
		return "<input type=\"checkbox\" name=\"razduzeno-zaduzenje[]\" value=\"{$this->$pk}\" data-tip=\"{$this->tip}\" data-iznos=\"{$this->zaRazduzenje()['ukupno']}\" class=\"razduzeno-zaduzenja\"{$chk}>";
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

	// ??? ***************************************************

	// koristi se samo u statistici

	// ovo i ako se koristi negde treba da se doda u WHERE - AND razduzeno = 0
	public function dugZaduzenjaZakup()
	{
		$sql = "SELECT SUM(iznos_zaduzeno-iznos_razduzeno) AS ukupno
				FROM {$this->table}
				WHERE tip = 'zakup' AND reprogram_id IS NULL;";
		$broj = $this->fetch($sql)[0]->ukupno;
		return (float) $broj;
	}

	// ovo i ako se koristi negde treba da se doda u WHERE - AND razduzeno = 0
	public function dugZaduzenjaTaksa()
	{
		$sql = "SELECT SUM(iznos_zaduzeno-iznos_razduzeno) AS ukupno
				FROM {$this->table}
				WHERE tip = 'taksa' AND reprogram_id IS NULL;";
		$broj = $this->fetch($sql)[0]->ukupno;
		return (float) $broj;
	}
	// ??? ***************************************************

	// vraca iznos za razduzenje zaduzenja (glavnica + kamata)
	// kmata se racuna od datum_prispeca do trenutnog datuma
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
