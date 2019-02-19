<?php

namespace App\Controllers;

use App\Models\Karton;

class KartoniController extends Controller
{
	public function getKartoni($request, $response, $args)
	{
		$query = [];
		parse_str($request->getUri()->getQuery(), $query);
		$page = isset($query['page']) ? (int)$query['page'] : 1;

		$model = new Karton();
		$kartoni = $model->paginate($page);

		$this->render($response, 'kartoni.twig', compact('kartoni'));
	}
}
