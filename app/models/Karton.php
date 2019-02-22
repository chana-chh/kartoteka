<?php

namespace App\Models;

use App\Classes\Model;

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
        return $this->hasMany('App\Models\Transakcija','karton_id','datum DESC');
        // $sql = "SELECT * FROM transakcije WHERE karton_id = {$this->id} ORDER BY datum DESC;";
        // return $this->fetch($sql, null, 'App\Models\Transakcija');
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

    public function saldo()
    { }
}
