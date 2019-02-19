<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Pokojnik extends Model
{
	protected $table = 'pokojnici';

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function datum_rodjenja()
	{
		return DateTime::createFromFormat('Y-m-d', $this->datum_rodjenja)->format('d.m.Y');
	}

	public function datum_smrti()
	{
		return DateTime::createFromFormat('Y-m-d', $this->datum_smrti)->format('d.m.Y');
	}

	public function datum_sahrane()
	{
		return DateTime::createFromFormat('Y-m-d', $this->datum_sahrane)->format('d.m.Y');
	}

	public function datum_ekshumacije()
	{
		return DateTime::createFromFormat('Y-m-d', $this->datum_ekshumacije)->format('d.m.Y');
	}
}
