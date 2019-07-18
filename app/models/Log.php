<?php

namespace App\Models;

use DateTime;
use App\Classes\Model;

class Log extends Model
{
	protected $table = 'logovi';

	public function datum()
    {
    	$format = 'Y-m-d H:i:s';
        if ($this->datum === null) {
            return "";
        } else {
            return DateTime::createFromFormat($format, $this->datum)->format('Y-m-d\TH:i:s');
        }
    }

    public function danas()
    {
    	$sql = "SELECT * FROM {$this->table} WHERE end >= CURDATE() AND end < DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
    	return $this->fetch($sql);
    }

}
