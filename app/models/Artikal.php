<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Artikal extends Model
{
	protected $table = 'artikli';

	// public function porez()
	// {
	// 	return $this->belongsTo('App\Models\Porez', 'porez_id');
	// }

	// public function racuni()
	// {
	// 	$this->belongsToMany('App\Models\Racun', 'racun_artikal', 'artikal_id', 'racun_id');
	// }

	// public function fiskal()
	// {
	// 	$chk = $this->fiskal === 1 ? ' checked' : '';
	// 	$pk = $this->pk;
	// 	return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk}>";
	// }

	// public function fiskalDisabled()
	// {
	// 	$chk = $this->fiskal === 1 ? ' checked' : '';
	// 	$pk = $this->pk;
	// 	return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk} disabled>";
	// }
}
