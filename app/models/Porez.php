<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Porez extends Model
{
	protected $table = 'porezi';

	public function artikli()
	{
		return $this->hasMany('App\Models\Artikal', 'porez_id');
	}
}
