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
}
