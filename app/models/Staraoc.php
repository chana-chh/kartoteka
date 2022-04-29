<?php

namespace App\Models;

use App\Classes\Model;

class Staraoc extends Model
{
    protected $table = 'staraoci';

    public function punoIme()
    {
        $si = empty($this->srednje_ime) ? "" : "({$this->srednje_ime}) ";
        return "{$this->prezime} {$si}{$this->ime}";
    }

    public function adresa()
    {
        return "{$this->mesto}, {$this->ulica} {$this->broj}";
    }

    public function karton()
    {
        return $this->belongsTo('App\Models\Karton', 'karton_id');
    }

    public function uplate()
    {
        return $this->hasMany('App\Models\Uplata', 'staraoc_id');
    }

    // prikaz chk aktivan na pogledu
    public function aktivan()
    {
        $chk = $this->aktivan === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk}>";
    }

    // prikaz chk aktivan na pogledu (nije moguce promeniti klikom)
    public function aktivanDisabled()
    {
        $chk = $this->aktivan === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk} disabled>";
    }

    // jedan prema vise na zaduzenje
    public function zaduzenja()
    {
        return $this->hasMany('App\Models\Zaduzenje', 'staraoc_id');
    }



    // new ****************************************************************************************************************************
    public function svaZaduzenja()
    {
        $sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id} ORDER BY godina ASC;";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function zaduzenaZaduzenja()
    {
        $sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id}
                AND razduzeno = 0 AND reprogram_id IS NULL ORDER BY godina ASC, tip ASC;";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function razduzenaZaduzenja()
    {
        $sql = "SELECT * FROM zaduzenja WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id}
                AND razduzeno = 1 ORDER BY godina ASC;";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    // takse

    public function taksaZaGodinu()
    {
        $cena = (float) (new Cena())->taksa();
        $br_mesta = $this->karton()->broj_mesta;
        $br_staraoca = $this->karton()->brojAktivnihStaraoca();
        $taksa = $cena * $br_mesta / $br_staraoca;
        return (float) $taksa;
    }

    public function sveTakse()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function zaduzeneTakse()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function razduzeneTakse()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 1
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function saldoZaTakse()
    {
        $sql = "SELECT SUM(iznos_razduzeno) AS saldo FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0 AND iznos_razduzeno > 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        $saldo = $this->fetch($sql)[0]->saldo;
        return round((float) $saldo, 2);
    }

    public function dugZaTakse()
    {
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        $broj = (int) $this->fetch($sql)[0]->broj;
        $cena = (float) (new Cena())->taksa();
        $br_mesta = $this->karton()->broj_mesta;
        $br_staraoca = $this->karton()->brojAktivnihStaraoca();
        $saldo = $this->saldoZaTakse();
        return round((float) ($broj * $cena * $br_mesta / $br_staraoca) - $saldo, 2);
    }

    public function taksePosleTekuceGodine()
    {
        $god = GOD;
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'taksa' AND razduzeno = 0
                AND godina > {$god} AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        $broj = (int) $this->fetch($sql)[0]->broj;
        return $broj;
    }

    // zakupi

    public function zakupZaGodinu()
    {
        $cena = (float) (new Cena())->zakup();
        $br_mesta = $this->karton()->broj_mesta;
        $br_staraoca = $this->karton()->brojAktivnihStaraoca();
        $zakup = $cena * $br_mesta / $br_staraoca;
        return (float) $zakup;
    }

    public function sviZakupi()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function zaduzeniZakupi()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function razduzeniZakupi()
    {
        $sql = "SELECT * FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 1
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Zaduzenje');
    }

    public function saldoZaZakupe()
    {
        $sql = "SELECT SUM(iznos_razduzeno) AS saldo FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0 AND iznos_razduzeno > 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        $saldo = $this->fetch($sql)[0]->saldo;
        return round((float) $saldo, 2);
    }

    public function dugZaZakupe()
    {
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        $broj = (int) $this->fetch($sql)[0]->broj;
        $cena = (float) (new Cena())->zakup();
        $br_mesta = (int) $this->karton()->broj_mesta;
        $br_staraoca = (int) $this->karton()->brojAktivnihStaraoca();
        $saldo = $this->saldoZaZakupe();
        return round((float) ($broj * $cena * $br_mesta / $br_staraoca) - $saldo, 2);
    }

    public function zakupiPosleTekuceGodine()
    {
        $god = GOD;
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE tip = 'zakup' AND razduzeno = 0
                AND godina > {$god} AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        $broj = (int) $this->fetch($sql)[0]->broj;
        return $broj;
    }

    // racuni

    public function racuni()
    {
        $sql = "SELECT * FROM racuni WHERE staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Racun');
    }

    public function sviRacuni()
    {
        $sql = "SELECT * FROM racuni WHERE karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Racun');
    }

    public function zaduzeniRacuni()
    {
        $sql = "SELECT * FROM racuni WHERE razduzeno = 0 AND reprogram_id IS NULL
                AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Racun');
    }

    public function razduzeniRacuni()
    {
        $sql = "SELECT * FROM racuni WHERE razduzeno = 1 AND reprogram_id IS NULL
                AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        return $this->fetch($sql, null, '\App\Models\Racun');
    }

    public function dugZaRacune()
    {
        $sql = "SELECT SUM(iznos) AS iznos FROM racuni WHERE razduzeno = 0
                AND reprogram_id IS NULL AND karton_id = {$this->karton()->id} AND staraoc_id = {$this->id};";
        $iznos = (float) $this->fetch($sql)[0]->iznos;
        return round($iznos, 2);
    }

    public function ukupanDug()
    {
        return $this->dugZaTakse() + $this->dugZaZakupe() + $this->dugZaRacune();
    }

    public function imaSaldo()
    {
        return $this->privremeni_saldo > 0 ? true : false;
    }
}
