<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Cena;
use App\Models\Groblje;
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

	public function getUneteUplate($request, $response)
	{
		$groblja = (new Groblje)->all();
		$this->render($response, 'unete_uplate.twig', compact('groblja'));
	}

	public function postUneteUplate($request, $response)
	{
		$data = $request->getParams();
		$groblje_id = $data['groblje_id'];
		$groblje = (new Groblje)->find($groblje_id);
		$parcele = $data['parcele'];
		if ($parcele !== "")
		{
			$parcele = str_replace(" ", "", $parcele);
			$parcele = explode(",", $parcele);
			$parcele = "'" . implode("','", $parcele) . "'";
			$sql = "SELECT * FROM kartoni WHERE groblje_id = {$groblje_id} AND parcela IN({$parcele}) ORDER BY parcela, grobno_mesto;";
			$kartoni = (new Karton)->fetch($sql);
			$groblje = strtoupper((new Groblje)->find($groblje_id)->naziv);
			$parcele = strtoupper(str_replace("'", "", $parcele));
			$this->render($response, 'unete_uplate_stampa.twig', compact('kartoni', 'groblje', 'parcele'));
		}
	}
}
