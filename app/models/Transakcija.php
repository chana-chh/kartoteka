<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Transakcija extends Model
{
    protected $table = 'transakcije';

    public function tip_transakcije()
    {
        return $this->belongsTo('App\Models\TipTransakcije', 'tip_transakcije_id');
    }

    public function datum()
    {
        return DateTime::createFromFormat('Y-m-d', $this->datum)->format('d.m.Y');
    }

    public function razduzeno()
    {
        $chk = $this->razduzeno === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"razduzeno\" data-id=\"{$this->$pk}\"{$chk}>";
    }

    public function razduzenoDisabled()
    {
        $chk = $this->razduzeno === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"razduzeno\" data-id=\"{$this->$pk}\"{$chk} disabled>";
    }
}
