<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Reprogram extends Model
{
	protected $table = 'reprogrami';

	public function razduzeno()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-reprogrami[]\" value=\"{$this->$pk}\" data-iznos=\"{$this->iznos}\" class=\"razduzeno-reprogrami\"{$chk}>";
	}

	public function razduzenoDisabled()
	{
		$chk = $this->razduzeno === 1 ? ' checked' : '';
		$pk = $this->pk;
		return "<input type=\"checkbox\" name=\"razduzeno-reprogrami[]\" value=\"{$this->$pk}\" data-iznos=\"{$this->iznos}\" class=\"razduzeno-reprogrami\"{$chk} disabled>";
	}

	public function datum()
	{
		if ($this->datum === null) {
			return "";
		} else {
			return DateTime::createFromFormat('Y-m-d', $this->datum)->format('d.m.Y');
		}
	}
}
