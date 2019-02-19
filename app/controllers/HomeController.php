<?php

namespace App\Controllers;

use App\Models\Karton;

class HomeController extends Controller
{
	public function getHome($request, $response)
	{
		$this->render($response, 'home.twig');
	}
}
