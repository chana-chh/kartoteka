<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Cena;
use App\Models\Pokojnik;
use App\Models\Staraoc;
use App\Models\Zaduzenje;
use App\Models\Racun;
use App\Models\Uplata;


class StatistikaController extends Controller
{
	public function getStatistika($request, $response)
	{
		$zaduzenje = new Zaduzenje();
		$racun = new Racun();
		$uplata = new Uplata();

		$karton = new Karton();
		$kartoni = count($karton->all());

		$pokojnik = new Pokojnik();
		$pokojnici = count($pokojnik->all());

		$staraoc = new Staraoc();
		$staraoci = count($staraoc->all());

		$broj_mesta = $karton->ukupanBrojMesta();
		$dugTakse = $zaduzenje->dugZaduzenjaTaksa();
		$dugZakupi = $zaduzenje->dugZaduzenjaZakup();
		$racuni = $racun->dugRacun();
		$uplate = $uplata->tekucaUplate();

		$this->render(
			$response,
			'statistika.twig',
			compact(
				'dugTakse',
				'dugZakupi',
				'racuni',
				'uplate',
				'kartoni',
				'pokojnici',
				'staraoci',
				'broj_mesta'
			)
		);
	}
}
