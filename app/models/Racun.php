<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Racun extends Model
{
	protected $table = 'racuni';

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
		$sql = "SELECT * FROM {$this->table} WHERE razduzeno = 0 AND rok IS NOT NULL AND rok >= CURDATE() AND rok < DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    	return $this->fetch($sql);
	}

	public function istekli()
	{
		$sql = "SELECT * FROM {$this->table} WHERE razduzeno = 0 AND rok IS NOT NULL AND rok < CURDATE()";
    	return $this->fetch($sql);
	}
}
