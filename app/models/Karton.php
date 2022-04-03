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

    public function uplata()
    {
        return $this->hasMany('App\Models\Karton', 'karton_id');
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
        $sql = "SELECT * FROM {$this->table} WHERE aktivan = 1;";
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
        $sql = "SELECT DISTINCT groblje_id,parcela FROM {$this->table}";
        return $this->fetch($sql);
    }


    // Transakcije
    public function uplate()
    {
        $sql = "SELECT * FROM uplate WHERE karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Uplata');
    }

    public function takse()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function nerazduzeneTakse()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0 AND reprogram_id IS NULL AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function dugZaTakse()
    {
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0 AND reprogram_id IS NULL AND karton_id = {$this->id};";
        $broj = $this->fetch($sql)[0]->broj;
        $model_cena = new Cena();
        $cena = (float) $model_cena->taksa();
        return $broj * $cena * $this->broj_mesta;
    }

    public function zakupi()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function nerazduzeniZakupi()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0 AND reprogram_id IS NULL AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function dugZaZakupe()
    {
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0 AND reprogram_id IS NULL AND karton_id = {$this->id};";
        $broj = $this->fetch($sql)[0]->broj;
        $model_cena = new Cena();
        $cena = (float) $model_cena->zakup() / 10;
        return $broj * $cena * $this->broj_mesta;
    }

    public function racuni()
    {
        $sql = "SELECT * FROM racuni WHERE karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Racun');
    }

    public function nerazduzeniRacuni()
    {
        $sql = "SELECT * FROM racuni WHERE razduzeno = 0 AND reprogram_id IS NULL AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Racun');
    }

    public function dugZaRacune()
    {
        $sql = "SELECT SUM(iznos) AS dug FROM racuni WHERE razduzeno = 0 AND reprogram_id IS NULL AND karton_id = {$this->id};";
        return (float) $this->fetch($sql)[0]->dug;
    }

    public function reprogrami()
    {
        $sql = "SELECT * FROM reprogrami WHERE karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Reprogram');
    }

    public function nerazduzeniReprogrami()
    {
        $sql = "SELECT * FROM reprogrami WHERE razduzeno = 0 AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Reprogram');
    }

    public function dugZaReprograme()
    {
        $sql = "SELECT SUM((iznos/period)*preostalo_rata) AS dug FROM reprogrami WHERE razduzeno = 0 AND karton_id = {$this->id};";
        return (float) $this->fetch($sql)[0]->dug;
    }

    public function dug()
    {
        return $this->dugZaTakse() + $this->dugZaZakupe() + $this->dugZaRacune() + $this->dugZaReprograme();
    }

    public function nerazduzeneTakseZaReprogram($reprogram_id)
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0 AND (reprogram_id = {$reprogram_id} OR reprogram_id IS NULL) AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function nerazduzeniZakupiZaReprogram($reprogram_id)
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0 AND (reprogram_id = {$reprogram_id} OR reprogram_id IS NULL) AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function nerazduzeniRacuniZaReprogram($reprogram_id)
    {
        $sql = "SELECT * FROM racuni WHERE razduzeno = 0 AND (reprogram_id = {$reprogram_id} OR reprogram_id IS NULL) AND karton_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Racun');
    }

    public function sumaUplata()
    {
        $sql = "SELECT SUM(iznos) AS ukupno FROM uplate WHERE karton_id = {$this->id};";
        return (float) $this->fetch($sql)[0]->ukupno;
    }

    //Za uvrnutu statistiku

    public function ukupanBrojMesta()
    {
        $sql = "SELECT SUM(broj_mesta) AS ukupno FROM {$this->table};";
        return $this->fetch($sql)[0]->ukupno;
    }

    public function brojNerazduzenihTaksi()
    {
        $sql = "SELECT SUM(d) AS ukupno FROM
                (SELECT ((SELECT taksa FROM cene WHERE vazece = 1)*kartoni.broj_mesta) AS d
                FROM zaduzenja 
                LEFT JOIN kartoni ON zaduzenja.karton_id = kartoni.id
                WHERE tip = 'taksa' AND razduzeno = 0 AND reprogram_id IS NULL) AS celo;";
        $broj = $this->fetch($sql)[0]->ukupno;
        return (float) $broj;
    }

    public function brojNerazduzenihZakupa()
    {
        $sql = "SELECT SUM(d) AS ukupno FROM
                (SELECT ((SELECT zakup FROM cene WHERE vazece = 1)*kartoni.broj_mesta) AS d
                FROM zaduzenja 
                LEFT JOIN kartoni ON zaduzenja.karton_id = kartoni.id
                WHERE tip = 'zakup' AND razduzeno = 0 AND reprogram_id IS NULL) AS celo;";
        $broj = $this->fetch($sql)[0]->ukupno;
        return (float) $broj;
    }

    public function ukupanDugZaRacune()
    {
        $sql = "SELECT SUM(iznos) AS dug FROM racuni WHERE razduzeno = 0 AND reprogram_id IS NULL;";
        return (float) $this->fetch($sql)[0]->dug;
    }

    public function ukupnaSumaUplata()
    {
        $sql = "SELECT SUM(iznos) AS ukupno FROM uplate;";
        return (float) $this->fetch($sql)[0]->ukupno;
    }

}
