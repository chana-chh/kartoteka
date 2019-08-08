<?php

namespace App\Models;

use App\Classes\Model;
use App\Classes\Db;

class Karton extends Model
{
    protected $table = 'kartoni';

    public function broj()
    {
        return $this->groblje()->naziv . '-' . $this->parcela . '-' . $this->grobno_mesto;
    }

    public function saldo()
    {
        return 0;
    }

    public function rasporedi()
    {
        return $this->hasMany('App\Models\Raspored', 'karton_id');
    }

    public function groblje()
    {
        return $this->belongsTo('App\Models\Groblje', 'groblje_id');
    }

    public function staraoci()
    {
        return $this->hasMany('App\Models\Staraoc', 'karton_id');
    }

    public function pokojnici()
    {
        return $this->hasMany('App\Models\Pokojnik', 'karton_id');
    }

    public function dokumenti()
    {
        return $this->hasMany('App\Models\Dokument', 'karton_id');
    }

    public function sviAktivni()
    {
        $sql = "SELECT id FROM {$this->table} WHERE aktivan = 1;";
        return $this->fetch($sql);
    }

    public function aktivan()
    {
        $chk = $this->aktivan === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk}>";
    }

    public function aktivanDisabled()
    {
        $chk = $this->aktivan === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk} disabled>";
    }

    /**
    * Vraca sve parcele iz kartona
    */

    public function vratiParcele()
    {
        $sql = "SELECT parcela FROM {$this->table} GROUP BY parcela";
        return $this->fetch($sql);
    }
}
