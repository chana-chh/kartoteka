<?php

namespace App\Models;

use App\Classes\Model;

class Zaduzenje extends Model
{
	protected $table = 'zaduzenja';

	public function takse()
	{
		$sql = "SELECT * FROM {$this->table} WHERE tip = 'taksa';";
		return $this->fetch($sql);
	}

	public function zakupi()
	{
		$sql = "SELECT * FROM {$this->table} WHERE tip = 'zakup';";
		return $this->fetch($sql);
	}

	public function takseZaKarton($karton_id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE tip = 'taksa' AND karton_id = {$karton_id};";
		return $this->fetch($sql);
	}

	public function zakupiZaKarton($karton_id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE tip = 'zakup' AND karton_id = {$karton_id};";
		return $this->fetch($sql);
	}

	public function nerazduzeneTakse()
	{
		$sql = "SELECT * FROM {$this->table} WHERE tip = 'taksa' AND razduzeno = 0;";
		return $this->fetch($sql);
	}

	public function nerazduzeniZakupi()
	{
		$sql = "SELECT * FROM {$this->table} WHERE tip = 'zakup' AND razduzeno = 0;";
		return $this->fetch($sql);
	}

	public function nerazduzeneTakseZaKarton($karton_id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE tip = 'taksa' AND razduzeno = 0 AND karton_id = {$karton_id};";
		return $this->fetch($sql);
	}

	public function nerazduzeniZakupiZaKarton($karton_id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE tip = 'zakup' AND razduzeno = 0 AND karton_id = {$karton_id};";
		return $this->fetch($sql);
	}

	
}
