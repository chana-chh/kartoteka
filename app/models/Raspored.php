<?php

namespace App\Models;

use DateTime;
use App\Classes\Model;

class Raspored extends Model
{
	protected $table = 'raspored';

    public function karton()
    {
        return $this->belongsTo('App\Models\Karton', 'karton_id');
    }

    public function pokojnik()
    {
        return $this->belongsTo('App\Models\Pokojnik', 'pokojnik_id');
    }

	public function datum_start()
    {
    	$format = 'Y-m-d H:i:s';
        if ($this->start === null) {
            return "";
        } else {
            return DateTime::createFromFormat($format, $this->start)->format('Y-m-d\TH:i:s');
        }
    }

    public function pocetak()
    {
        $format = 'Y-m-d H:i:s';
        if ($this->start === null) {
            return "";
        } else {
            return DateTime::createFromFormat($format, $this->start)->format('d-m-Y H:i');
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
