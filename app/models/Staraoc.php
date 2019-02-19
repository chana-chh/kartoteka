<?php

namespace App\Models;

use App\Classes\Model;

class Staraoc extends Model
{
	protected $table = 'staraoci';

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}
}
