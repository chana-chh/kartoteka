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

    public function aktivan()
    {
        $chk = $this->aktivan === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk}>";
    }

    public function aktivanDisabled()
    {
        $chk = $this->aktivan === 1 ? ' checked' : '';
        $pk = $this->pk;
        return "<input type=\"checkbox\" name=\"aktivan\" data-id=\"{$this->$pk}\"{$chk} disabled>";
    }
}
