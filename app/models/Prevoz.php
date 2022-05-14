<?php

namespace App\Models;

use App\Classes\Model;

class Prevoz extends Model
{
    protected $table = 'prevozi';

	public function korisnik()
    {
        return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
    }

	public function zaDatum($datum = null)
	{
		$datum = ($datum === null) ? date('Y-m-d') : $datum;;

		$sql = "SELECT * FROM prevozi WHERE datum = :datum ORDER BY vreme ASC";
		return $this->fetch($sql, [':datum' => $datum]);
	}

    public function punoIme()
    {
        $si = empty($this->srednje_ime) ? "" : "({$this->srednje_ime}) ";
        return "{$this->prezime} {$si}{$this->ime}";
    }

    public function punoImePokojnika()
    {
        $si = empty($this->pok_srednje_ime) ? "" : "({$this->pok_srednje_ime}) ";
        return "{$this->pok_prezime} {$si}{$this->pok_ime}";
    }

    public function adresaOd()
    {
		$ptt = empty($this->od_ptt) ? "" : "({$this->od_ptt}) ";
        return "{$ptt}{$this->od_mesto}, {$this->od_ulica} {$this->od_broj}";
    }
    
	public function adresaDo()
    {
		$ptt = empty($this->do_ptt) ? "" : "({$this->do_ptt}) ";
        return "{$ptt}{$this->do_mesto}, {$this->do_ulica} {$this->do_broj}";
    }
}
