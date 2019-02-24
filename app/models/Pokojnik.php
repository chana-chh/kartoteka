<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Pokojnik extends Model
{
    protected $table = 'pokojnici';

    public function punoIme()
    {
        $si = empty($this->srednje_ime) ? "" : "({$this->srednje_ime}) ";
        return "{$this->prezime} {$si}{$this->ime}";
    }

    public function duplaRaka(bool $disabled = false)
    {
        $chk = $this->dupla_raka === 1 ? ' checked' : '';
        $pk = $this->pk;
        $d = $disabled ? " disabled" : "";
        return "<input type=\"checkbox\" name=\"dupla_raka\" data-id=\"{$this->$pk}\"{$chk}{$d}>";
    }

    public function karton()
    {
        return $this->belongsTo('App\Models\Karton', 'karton_id');
    }

    public function datum_rodjenja()
    {
        if ($this->datum_rodjenja === null) {
            return "";
        } else {
            return DateTime::createFromFormat('Y-m-d', $this->datum_rodjenja)->format('d.m.Y');
        }
    }

    public function datum_smrti()
    {
        if ($this->datum_smrti === null) {
            return "";
        } else {
            return DateTime::createFromFormat('Y-m-d', $this->datum_smrti)->format('d.m.Y');
        }
    }

    public function datum_sahrane()
    {
        if ($this->datum_sahrane === null) {
            return "";
        } else {
            return DateTime::createFromFormat('Y-m-d', $this->datum_sahrane)->format('d.m.Y');
        }
    }

    public function datum_ekshumacije()
    {
        if ($this->datum_ekshumacije === null) {
            return "";
        } else {
            return DateTime::createFromFormat('Y-m-d', $this->datum_ekshumacije)->format('d.m.Y');
        }
    }
}
