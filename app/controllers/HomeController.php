<?php

namespace App\Controllers;

use App\Models\Raspored;
use App\Models\Karton;
use App\Models\Pokojnik;
use App\Models\Staraoc;
use App\Models\Racun;
use App\Models\Cena;

class HomeController extends Controller
{
	public function getHome($request, $response)
	{
		$model = new Raspored();
		$danasnji = $model->danas();

		$karton = new Karton();
		$kartoni = count($karton->all());

		$pokojnik = new Pokojnik();
		$pokojnici = count($pokojnik->all());

		$staraoc = new Staraoc();
		$staraoci = count($staraoc->all());

		$racun = new Racun();
		$isticu = $racun->rok();
		$istekli = $racun->istekli();

		// proveriti prestupnu godinu i srediri tabelu kamate

		// if (date('L') === 1) // prestupna godina
		// {
		// 	// preuzeti poslednju kamatu
		// 	// 
		// }

		$this->render($response, 'home.twig', compact('danasnji', 'kartoni', 'pokojnici', 'staraoci', 'isticu', 'istekli'));
	}

	public function getAbout($request, $response)
	{
		$this->render($response, 'about.twig');
	}

	public function getHelp($request, $response)
	{
		$this->render($response, 'help.twig');
	}
	public function getHelpKartoni($request, $response)
	{
		$this->render($response, 'help_kartoni.twig');
	}
	public function getHelpStaraoci($request, $response)
	{
		$this->render($response, 'help_staraoci.twig');
	}
	public function getHelpPokojnici($request, $response)
	{
		$this->render($response, 'help_pokojnici.twig');
	}
	public function getHelpAdmin($request, $response)
	{
		$this->render($response, 'help_admin.twig');
	}

	public function getHelpTransakcije($request, $response)
	{
		$this->render($response, 'help_transakcije.twig');
	}
}
