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

    public function transakcije()
    {
        return $this->hasMany('App\Models\Transakcija', 'karton_id', 'datum DESC');
    }

    public function mapa()
    {
        return $this->hasOne('App\Models\Mapa', 'karton_id');
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

    public function saldo()
    {
        $sql = "SELECT SUM(iznos) AS saldo FROM transakcije WHERE karton_id = {$this->id};";
        return (float)Db::fetch($sql)[0]->saldo;
    }

    public function nerazduzeneTransakcije()
    {
        $pk = $this->getPrimaryKey();
        $sql = "SELECT * FROM transakcije
                WHERE karton_id = {$this->$pk}
                AND tip_transakcije_id > 1
                AND razduzeno = 0
                ORDER BY datum DESC;";
        return $this->fetch($sql, null, 'App\Models\Transakcija');
    }
}
