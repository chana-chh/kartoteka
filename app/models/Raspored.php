<?php

namespace App\Models;

use DateTime;
use App\Classes\Model;

class Raspored extends Model
{
	protected $table = 'raspored';

	public function datum_start()
    {
    	$format = 'Y-m-d H:i:s';
        if ($this->start === null) {
            return "";
        } else {
            return DateTime::createFromFormat($format, $this->start)->format('Y-m-d\TH:i:s');
        }
    }

    public function datum_end()
    {
    	$format = 'Y-m-d H:i:s';
        if ($this->end === null) {
            return "";
        } else {
            return DateTime::createFromFormat($format, $this->end)->format('Y-m-d\TH:i:s');
        }
    }

    public function danas()
    {
    	$sql = "SELECT * FROM {$this->table} WHERE end >= CURDATE() AND end < DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
    	return $this->fetch($sql);
    }
}