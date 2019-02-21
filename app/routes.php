<?php

/**
 * ChaSha - Putanje (rute) aplikacije
 *
 * Sve putanje aplikacije
 *
 * @version v 0.0.1
 * @author ChaSha
 * @copyright Copyright (c) 2019, ChaSha
 */

use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;

$app->get('/', '\App\Controllers\HomeController:getHome')->setName('pocetna');

$app->group('', function () {
	$this->get('/prijava', '\App\Controllers\AuthController:getPrijava')->setName('prijava');
	$this->post('/prijava', '\App\Controllers\AuthController:postPrijava');
})->add(new GuestMiddleware($container));

$app->group('', function () {
	$this->get('/odjava', '\App\Controllers\AuthController:getOdjava')->setName('odjava');
	$this->get('/kartoni', '\App\Controllers\KartoniController:getKartoni')->setName('kartoni');
	$this->get('/kartoni/pretraga', '\App\Controllers\KartoniController:getKartoniPretraga')->setName('kartoni.pretraga');
	$this->post('/kartoni/pretraga', '\App\Controllers\KartoniController:postKartoniPretraga');
})->add(new AuthMiddleware($container));
