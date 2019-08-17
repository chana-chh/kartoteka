<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Karton;
use App\Models\Zaduzenje;
use App\Models\Racun;
use App\Models\Reprogram;

class ReprogramiController extends Controller
{
	public function getKartonReprogrami($request, $response, $args)
	{
		$karton_id = $args['id'];
		$model_karton = new Karton();
		$karton = $model_karton->find($karton_id);

		$this->render($response, 'transakcije_reprogrami.twig', compact('karton'));
	}

	public function getReprogramDodavanje($request, $response, $args)
	{
		$karton_id = $args['id'];
		$model_karton = new Karton();
		$karton = $model_karton->find($karton_id);

		$model_cene = new Cena();
		$cena_takse = $model_cene->taksa();
		$cena_zakupa = $model_cene->zakup() / 10;

		$this->render($response, 'reprogram_dodavanje.twig', compact('karton', 'cena_takse', 'cena_zakupa'));
	}

	public function postReprogramDodavanje($request, $response)
	{
		$data = $request->getParams();
		dd($data);
	}

	public function getReprogramIzmena($request, $response, $args)
	{
		$reprogram_id = $args['id'];
		$model_reprogram = new Reprogram();
		$reprogram = $model_reprogram->find($reprogram_id);

		$model_cene = new Cena();
		$cena_takse = $model_cene->taksa();
		$cena_zakupa = $model_cene->zakup() / 10;

		$this->render($response, 'reprogram_izmena.twig', compact('reprogram', 'cena_takse', 'cena_zakupa'));
	}

	public function postReprogramIzmena($request, $response)
	{
		$data = $request->getParams();
		dd($data);
	}
}
