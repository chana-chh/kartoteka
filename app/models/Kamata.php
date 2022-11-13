<?php

namespace App\Models;

use App\Classes\Model;
use DateTime;

class Kamata extends Model
{
	protected $table = 'kamate';

	// vraca sve periode i kamate izmedju dva datuma
	public function kamateZaObracun($od, $do)
	{
		$sql = "SELECT * FROM kamate WHERE datum > :od AND datum < :do ORDER BY datum ASC;";
		$kamate = $this->fetch($sql, [':od' => $od, ':do' => $do]);

		$rez = []; // od, do, dana, procenat, godina

		$dat_od = new DateTime($od);
		$dat_do = null;
		$procenat = 0;
		$godina = 0;

		if (empty($kamate))
		{
			return $rez;
		}

		foreach ($kamate as $k)
		{

			$dat_do = new DateTime($k->datum);
			$razlika = $dat_do->diff($dat_od);
			$procenat = $k->procenat;
			$godina = $k->dani;
			$rez[] = [
				'od' => $dat_od->format('Y-m-d'),
				'do' => $dat_do->format('Y-m-d'),
				'dana' => $razlika->days,
				'procenat' => round($procenat, 2),
				'godina' => $godina,
			];
			$dat_od = new DateTime($k->datum);
		}

		$dat_do = new DateTime($do);
		$razlika = $dat_do->diff($dat_od);

		$rez[] = [
			'od' => $dat_od->format('Y-m-d'),
			'do' => $dat_do->format('Y-m-d'),
			'dana' => $razlika->days,
			'procenat' => round($procenat, 2),
			'godina' => $godina,
		];

		return $rez;
	}

	// vraca datum u obliku ('d.m.Y')
	public function datum()
	{
		$format = 'Y-m-d';
		if ($this->datum === null)
		{
			return "";
		}
		else
		{
			return DateTime::createFromFormat($format, $this->datum)->format('d.m.Y');
		}
	}

	// vraca datum u obliku ('Y')
	public function godina()
	{
		$format = 'Y-m-d';
		if ($this->datum === null)
		{
			return "";
		}
		else
		{
			return DateTime::createFromFormat($format, $this->datum)->format('Y');
		}
	}


	public function sredi()
	{
		// mora da se doda novi zapis
		// $sql = "SELECT * from {$this->table} order by datum DESC limit 1";
		// $poslednja = $this->fetch($sql)[0];
		// $d = 365;
		// if (date('L') === 1)
		// {
		// 	$d = 366;
		// }
		// if ($poslednja->godina() <= date('Y'))
		// {
		// 	$sqlb = "UPDATE {$this->table}
		// 	SET dani = {$d}
		// 	WHERE id = {$poslednja->id}";
		// 	$this->run($sqlb);
		// }
	}
}
