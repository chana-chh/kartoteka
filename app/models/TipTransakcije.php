<?php

namespace App\Models;

use App\Classes\Model;

class TipTransakcije extends Model
{
	protected $table = 'tipovi_transakcija';

	public function transakcije()
	{
		return $this->hasMany('App\Models\Transakcija', 'tip_transakcije_id');
	}
}
