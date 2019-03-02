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
    // Odjava
    $this->get('/odjava', '\App\Controllers\AuthController:getOdjava')->setName('odjava');
    // Kartoni
    $this->get('/kartoni', '\App\Controllers\KartoniController:getKartoni')->setName('kartoni');
    $this->get('/kartoni/pretraga', '\App\Controllers\KartoniController:getKartoniPretraga')->setName('kartoni.pretraga');
    $this->post('/kartoni/pretraga', '\App\Controllers\KartoniController:postKartoniPretraga');
    $this->get('/kartoni/pregled/{id}', '\App\Controllers\KartoniController:getKartoniPregled')->setName('kartoni.pregled');
    $this->get('/kartoni/dodavanje', '\App\Controllers\KartoniController:getKartoniDodavanje')->setName('kartoni.dodavanje');
    $this->post('/kartoni/dodavanje', '\App\Controllers\KartoniController:postKartoniDodavanje');
    //Mape
    $this->get('/mape', '\App\Controllers\MapeController:getMape')->setName('mape');
    $this->post('/mapa/dodavanje', '\App\Controllers\MapeController:postUpload')->setName('mapa.dodavanje');
    // Staraoci
    $this->get('/staraoci', '\App\Controllers\StaraociController:getStaraoci')->setName('staraoci');
    $this->get('/staraoci/pretraga', '\App\Controllers\StaraociController:getStaraociPretraga')->setName('staraoci.pretraga');
    $this->post('/staraoci/pretraga', '\App\Controllers\StaraociController:postStaraociPretraga');
    // Pokojnici
    $this->get('/pokojnici', '\App\Controllers\PokojniciController:getPokojnici')->setName('pokojnici');
    $this->get('/pokojnici/pretraga', '\App\Controllers\PokojniciController:getPokojniciPretraga')->setName('pokojnici.pretraga');
    $this->post('/pokojnici/pretraga', '\App\Controllers\PokojniciController:postPokojniciPretraga');
    // Transakcije
    $this->get('/kartoni/transakcije/{id}', '\App\Controllers\TransakcijeController:getTransakcijeKarton')->setName('kartoni.transakcije');
    $this->post('/kartoni/transakcije/razduzivanje/ajax', '\App\Controllers\TransakcijeController:ajaxRazduzivanje')->setName('transakcije.razduzivanje.ajax');
})->add(new AuthMiddleware($container));
