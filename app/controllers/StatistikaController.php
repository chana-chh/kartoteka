<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Cena;
use App\Models\Pokojnik;
use App\Models\Staraoc;


class StatistikaController extends Controller
{
	public function getStatistika($request, $response)
	{

        $karton = new Karton();

        $karton = new Karton();
        $kartoni = count($karton->all());

        $pokojnik = new Pokojnik();
        $pokojnici = count($pokojnik->all());

        $staraoc = new Staraoc();
        $staraoci = count($staraoc->all());

        $broj_mesta = $karton->ukupanBrojMesta();
        $dugTakse = $karton->brojNerazduzenihTaksi();
        $dugZakupi = $karton->brojNerazduzenihZakupa();
        $racuni = $karton->ukupanDugZaRacune();
        $uplate = $karton->ukupnaSumaUplata();
        
        $this->render($response, 'statistika.twig', compact('dugTakse', 'dugZakupi', 'racuni', 'uplate', 'kartoni', 
                        'pokojnici', 'staraoci', 'broj_mesta'));
	}
}
