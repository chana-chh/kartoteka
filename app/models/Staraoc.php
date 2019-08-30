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

    public function sukorisnik()
    {
        $chk = $this->sukorisnik === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"sukorisnik\" data-id=\"{$this->$pk}\"{$chk}>";
    }

    public function sukorisnikDisabled()
    {
        $chk = $this->sukorisnik === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"sukorisnik\" data-id=\"{$this->$pk}\"{$chk} disabled>";
    }
}
