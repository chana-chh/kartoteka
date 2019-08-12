<?php

namespace App\Models;

use App\Classes\Model;

class Cena extends Model
{
	protected $table = 'cene';

	public function vazeca()
	{
		$chk = $this->vazece === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"vazece\" data-id=\"{$this->$pk}\"{$chk}>";
	}

	public function vazecaDisabled()
	{
		$chk = $this->vazece === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"vazece\" data-id=\"{$this->$pk}\"{$chk} disabled>";
	}

	public function taksa()
	{
		$sql = "SELECT taksa FROM {$this->table} WHERE vazece = 1 LIMIT 1";
		return $this->fetch($sql)[0]->taksa;
	}

	public function zakup()
	{
		$sql = "SELECT zakup FROM {$this->table} WHERE vazece = 1 LIMIT 1";
		return $this->fetch($sql)[0]->zakup;
	}

	public function odrediVazece()
	{
		$sql_anuliraj = "UPDATE {$this->table} SET vazece = 0 WHERE vazece = 1";
		$this->run($sql_anuliraj);
		$sql_vrati = "SELECT * FROM {$this->table} WHERE datum = (SELECT MAX(datum) FROM {$this->table})";
		return $this->fetch($sql_vrati)[0];
	}
}
