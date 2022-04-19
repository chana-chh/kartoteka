<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Cena extends Model
{
	protected $table = 'cene';

	// prikaz vazece cene na pogledu
	public function vazeca()
	{
		$chk = $this->vazece === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"vazece\" data-id=\"{$this->$pk}\"{$chk}>";
	}

	// prikaz vazece cene na pogledu (nije moguce promeniti klikom)
	public function vazecaDisabled()
	{
		$chk = $this->vazece === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"vazece\" data-id=\"{$this->$pk}\"{$chk} disabled>";
	}

	// vraca iznos trenutno vazece takse
	public function taksa()
	{
		$sql = "SELECT taksa FROM {$this->table} WHERE vazece = 1 LIMIT 1";
		return $this->fetch($sql) ? (float) $this->fetch($sql)[0]->taksa : 0;
	}

	// vraca iznos trenutno vazeceg zakupa
	public function zakup()
	{
		$sql = "SELECT zakup FROM {$this->table} WHERE vazece = 1 LIMIT 1";
		return $this->fetch($sql) ? (float) $this->fetch($sql)[0]->zakup : 0;
	}

	// postavlja vazece cene na poslednji (najveci) datum
	public function odrediVazece()
	{
		$sql_anuliraj = "UPDATE {$this->table} SET vazece = 0 WHERE vazece = 1";
		$this->run($sql_anuliraj);
		$sql_vrati = "UPDATE {$this->table} SET vazece = 1 ORDER BY datum DESC LIMIT 1";
		$this->run($sql_vrati);
	}

	// vraca datum u obliku ('d.m.Y')
	public function datum()
    {
        $format = 'Y-m-d';
        if ($this->datum === null) {
            return "";
        } else {
            return DateTime::createFromFormat($format, $this->datum)->format('d.m.Y');
        }
    }

	// vraca datum u obliku ('Y')
    public function godina()
    {
        $format = 'Y-m-d';
        if ($this->datum === null) {
            return "";
        } else {
            return DateTime::createFromFormat($format, $this->datum)->format('Y');
        }
    }
}
