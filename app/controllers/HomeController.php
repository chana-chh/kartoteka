<?php

namespace App\Controllers;

use App\Models\Karton;

class HomeController extends Controller
{
	public function getHome($request, $response)
	{
		$this->render($response, 'home.twig');
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
}
