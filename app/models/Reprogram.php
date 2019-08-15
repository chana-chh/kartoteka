<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Reprogram extends Model
{
	protected $table = 'reprogrami';

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
		return (float) ($this->iznos / $this->period) * $this->preostalo_rata;
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
}
