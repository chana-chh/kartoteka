<?php

namespace App\Models;

use DateTime;
use App\Classes\Model;

class Dokument extends Model
{
	protected $table = 'dokumenta';

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function datum()
	{
		return DateTime::createFromFormat('Y-m-d', $this->datum)->format('d.m.Y');
	}
}
