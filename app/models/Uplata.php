<?php

namespace App\Models;

use App\Classes\Model;

class Uplata extends Model
{
	protected $table = 'uplate';

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function staraoc()
	{
		return $this->belongsTo('App\Models\Staraoc', 'staraoc_id');
	}

	public function korisnik()
	{
		return $this->belongsTo('App\Models\Korisnik', 'korisnik_id');
	}

	public function reprogram()
	{
		$reprogram_id = $this->reprogram_id ?? $this->reprogram_id;

		if ($reprogram_id === null)
		{
			return null;
		}

		$reprogram = (new Reprogram)->find($reprogram_id);

		return $reprogram;
	}
}
