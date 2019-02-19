<?php

namespace App\Models;

use App\Classes\Model;

class Mapa extends Model
{
	protected $table = 'mape';

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function groblje()
	{
		return $this->belongsTo('App\Models\Groblje', 'groblje_id');
	}
}
