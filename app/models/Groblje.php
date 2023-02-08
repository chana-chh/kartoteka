<?php

namespace App\Models;

use App\Classes\Model;

class Groblje extends Model
{
	protected $table = 'groblja';

	public function kartoni()
	{
		return $this->hasMany('App\Models\Karton', 'groblje_id');
	}

	public function respored()
	{
		return $this->hasMany('App\Models\Raspored', 'groblje_id');
	}

	public function mape()
	{
		return $this->hasMany('App\Models\Mapa', 'groblje_id');
	}
}
