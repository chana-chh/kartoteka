<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

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
		$sql_vrati = "UPDATE {$this->table} SET vazece = 1 ORDER BY datum DESC LIMIT 1";
		$this->run($sql_vrati);
	}

	public function datum()
    {
        $format = 'Y-m-d';
        if ($this->datum === null) {
            return "";
        } else {
            return DateTime::createFromFormat($format, $this->datum)->format('d.m.Y');
        }
    }
}
