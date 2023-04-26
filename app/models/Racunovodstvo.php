<?php

namespace App\Models;

use App\Classes\Model;

class Racunovodstvo extends Model
{
	protected $table = 'racunovodstvo';

	public function adresa()
	{
		return $this->mesto . ', ' . $this->ulica . ' ' . $this->broj;
	}
}
