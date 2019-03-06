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
$app->get('/o-programu', '\App\Controllers\HomeController:getAbout')->setName('o_programu');
$app->get('/uputstvo', '\App\Controllers\HomeController:getHelp')->setName('uputstvo');

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
    $this->post('/kartoni/brisanje', '\App\Controllers\KartoniController:postKartoniBrisanje')->setName('kartoni.brisanje');
    $this->get('/kartoni/izmena/{id}', '\App\Controllers\KartoniController:getKartoniIzmena')->setName('kartoni.izmena');
    $this->post('/kartoni/izmena', '\App\Controllers\KartoniController:postKartoniIzmena')->setName('kartoni.izmena.post');
    // Dokumenti
    $this->get('/karton/dokument/dodavanje/{id}', '\App\Controllers\DokumentiController:getDokumentiDodavanje')->setName('dokumenti.dodavanje');
    $this->post('/karton/dokument/dodavanje', '\App\Controllers\DokumentiController:postDokumentiDodavanje')->setName('dokumenti.dodavanje.post');
    $this->post('/karton/dokument/brisanje', '\App\Controllers\DokumentiController:postDokumentiBrisanje')->setName('dokumenti.brisanje');
    $this->get('/karton/dokument/izmena/{id}', '\App\Controllers\DokumentiController:getDokumentiIzmena')->setName('dokumenti.izmena');
    $this->post('/kartoni/dokument/izmena', '\App\Controllers\DokumentiController:postDokumentiIzmena')->setName('dokumenti.izmena.post');
    //Mape
    $this->get('/mape', '\App\Controllers\MapeController:getMape')->setName('mape');
    $this->get('/kartoni/mapa/{id}', '\App\Controllers\KartoniController:getKartoniMapa')->setName('kartoni.mapa');
    $this->post('/kartoni/mapa/dodavanje', '\App\Controllers\KartoniController:postKartoniMapa')->setName('kartoni.mapa.dodavanje');
    $this->post('/mapa/dodavanje', '\App\Controllers\MapeController:postUpload')->setName('mapa.dodavanje');
    $this->post('/mapa/brisanje', '\App\Controllers\MapeController:postMapaBrisanje')->setName('mapa.brisanje');
    // Staraoci
    $this->get('/staraoci', '\App\Controllers\StaraociController:getStaraoci')->setName('staraoci');
    $this->get('/staraoci/pretraga', '\App\Controllers\StaraociController:getStaraociPretraga')->setName('staraoci.pretraga');
    $this->post('/staraoci/pretraga', '\App\Controllers\StaraociController:postStaraociPretraga');
    $this->get('/staraoci/dodavanje/{id}', '\App\Controllers\StaraociController:getStaraociDodavanje')->setName('staraoci.dodavanje');
    $this->post('/staraoci/dodavanje', '\App\Controllers\StaraociController:postStaraociDodavanje')->setName('staraoci.dodavanje.post');
    $this->post('/staraoci/brisanje', '\App\Controllers\StaraociController:postStaraociBrisanje')->setName('staraoci.brisanje');
    $this->get('/staraoci/izmena/{id}', '\App\Controllers\StaraociController:getStaraociIzmena')->setName('staraoci.izmena');
    $this->post('/staraoci/izmena', '\App\Controllers\StaraociController:postStaraociIzmena')->setName('staraoci.izmena.post');
    // Pokojnici
    $this->get('/pokojnici', '\App\Controllers\PokojniciController:getPokojnici')->setName('pokojnici');
    $this->get('/pokojnici/pretraga', '\App\Controllers\PokojniciController:getPokojniciPretraga')->setName('pokojnici.pretraga');
    $this->post('/pokojnici/pretraga', '\App\Controllers\PokojniciController:postPokojniciPretraga');
    // Transakcije
    $this->get('/kartoni/transakcije/{id}', '\App\Controllers\TransakcijeController:getTransakcijeKarton')->setName('kartoni.transakcije');
    $this->post('/kartoni/transakcije/razduzivanje/ajax', '\App\Controllers\TransakcijeController:ajaxRazduzivanje')->setName('transakcije.razduzivanje.ajax');
})->add(new AuthMiddleware($container));
