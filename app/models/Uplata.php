<?php

namespace App\Models;

use App\Classes\Model;
use App\Classes\Db;

class Uplata extends Model
{
	protected $table = 'uplate';

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function korisnik()
	{
		return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
	}

}
