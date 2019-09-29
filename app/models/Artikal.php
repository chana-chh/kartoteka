<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Artikal extends Model
{
	protected $table = 'artikli';

	public function porez()
	{
		return $this->belongsTo('App\Models\Porez', 'porez_id');
	}

	public function racuni()
	{
		$this->belongsToMany('App\Models\Racun', 'racun_artikal', 'artikal_id', 'racun_id');
	}
}
