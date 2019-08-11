<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Reprogrami extends Model
{
	protected $table = 'reprogrami';

	public function reprogramiZaKarton($karton_id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE karton_id = {$karton_id};";
		return $this->fetch($sql);
	}

	public function nerazduzeniReprogrami()
	{
		$sql = "SELECT * FROM {$this->table} WHERE razduzeno = 0;";
		return $this->fetch($sql);
	}

	public function nerazduzeniReprogramiZaKarton($karton_id)
	{
		$sql = "SELECT * FROM {$this->table}
				WHERE razduzeno = 0 AND karton_id = {$karton_id};";
		return $this->fetch($sql);
	}

	public function razduzeno()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-reprogrami[]\" value=\"{$this->$pk}\" data-iznos=\"{$this->iznos}\" class=\"razduzeno-reprogrami\"{$chk}>";
	}

	public function datum()
	{
		if ($this->datum === null) {
			return "";
		} else {
			return DateTime::createFromFormat('Y-m-d', $this->datum)->format('d.m.Y');
		}
	}

	public function dugZaKarton($karton_id)
	{
		$sql = "SELECT SUM(iznos) AS dug FROM {$this->table} WHERE razduzeno = 0 AND karton_id = {$karton_id};";
		return $this->fetch($sql)[0]->dug;
	}
}
