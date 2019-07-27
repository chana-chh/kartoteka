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
$app->get('/uputstvo-kartoni', '\App\Controllers\HomeController:getHelpKartoni')->setName('uputstvo.kartoni');
$app->get('/uputstvo-staraoci', '\App\Controllers\HomeController:getHelpStaraoci')->setName('uputstvo.staraoci');
$app->get('/uputstvo-pokojnici', '\App\Controllers\HomeController:getHelpPokojnici')->setName('uputstvo.pokojnici');
$app->get('/uputstvo-administracija', '\App\Controllers\HomeController:getHelpAdmin')->setName('uputstvo.admin');
$app->get('/uputstvo-transakcije', '\App\Controllers\HomeController:getHelpTransakcije')->setName('uputstvo.transakcije');

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
    //Raspored
    $this->get('/raspored', '\App\Controllers\RasporedController:getRaspored')->setName('raspored');
    $this->get('/raspored/dodavanje', '\App\Controllers\RasporedController:getRasporedDodavanje')->setName('raspored.dodavanje');
    $this->post('/raspored/dodavanje', '\App\Controllers\RasporedController:postRasporedDodavanje')->setName('raspored.dodavanje.post');
    $this->post('/raspored/brisanje', '\App\Controllers\RasporedController:postRasporedBrisanje')->setName('raspored.brisanje');
    $this->get('/raspored/izmena/{id}', '\App\Controllers\RasporedController:getRasporedIzmena')->setName('raspored.izmena');
    $this->post('/raspored/izmena', '\App\Controllers\RasporedController:postRasporedIzmena')->setName('raspored.izmena.post');
    $this->get('/raspored/stampa/{id}', '\App\Controllers\RasporedController:getRasporedStampa')->setName('raspored.stampa');
    //Mape
    $this->get('/mape', '\App\Controllers\MapeController:getMape')->setName('mape');
    $this->get('/kartoni/mapa/{id}', '\App\Controllers\KartoniController:getKartoniMapa')->setName('kartoni.mapa');
    $this->post('/kartoni/mapa/dodavanje', '\App\Controllers\KartoniController:postKartoniMapa')->setName('kartoni.mapa.dodavanje');
    $this->post('/mapa/dodavanje', '\App\Controllers\MapeController:postUpload')->setName('mapa.dodavanje');
    $this->post('/mapa/brisanje', '\App\Controllers\MapeController:postMapaBrisanje')->setName('mapa.brisanje');
    $this->get('/mapa/izmena/{id}', '\App\Controllers\MapeController:getMapaIzmena')->setName('mapa.izmena');
    $this->post('/mapa/izmena', '\App\Controllers\MapeController:postMapaIzmena')->setName('mapa.izmena.post');
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
    $this->get('/pokojnici/dodavanje/{id}', '\App\Controllers\PokojniciController:getPokojniciDodavanje')->setName('pokojnici.dodavanje');
    $this->post('/pokojnici/dodavanje', '\App\Controllers\PokojniciController:postPokojniciDodavanje')->setName('pokojnici.dodavanje.post');
    $this->post('/pokojnici/brisanje', '\App\Controllers\PokojniciController:postPokojniciBrisanje')->setName('pokojnici.brisanje');
    $this->get('/pokojnici/izmena/{id}', '\App\Controllers\PokojniciController:getPokojniciIzmena')->setName('pokojnici.izmena');
    $this->post('/pokojnici/izmena', '\App\Controllers\PokojniciController:postPokojniciIzmena')->setName('pokojnici.izmena.post');
    // Logovi
    $this->get('/administracija/logovi', '\App\Controllers\LogController:getLog')->setName('logovi');
    // Korisnici
    $this->get('/administracija/korisnici', '\App\Controllers\KorisniciController:getKorisnici')->setName('korisnici');
    $this->post('/administracija/korisnici/dodavanje', '\App\Controllers\KorisniciController:postKorisniciDodavanje')->setName('korisnici.dodavanje');
    $this->post('/administracija/korisnici/brisanje', '\App\Controllers\KorisniciController:postKorisniciBrisanje')->setName('korisnici.brisanje');
    $this->get('/administracija/korisnici/izmena/{id}', '\App\Controllers\KorisniciController:getKorisniciIzmena')->setName('korisnici.izmena');
    $this->post('/administracija/korisnici/izmena', '\App\Controllers\KorisniciController:postKorisniciIzmena')->setName('korisnici.izmena.post');// Groblja
    $this->get('/administracija/groblja', '\App\Controllers\GrobljaController:getGroblja')->setName('groblja');
    $this->post('/administracija/groblja/dodavanje', '\App\Controllers\GrobljaController:postGrobljaDodavanje')->setName('groblja.dodavanje');
    $this->post('/administracija/groblja/brisanje', '\App\Controllers\GrobljaController:postGrobljaBrisanje')->setName('groblja.brisanje');
    $this->get('/administracija/groblja/izmena/{id}', '\App\Controllers\GrobljaController:getGrobljaIzmena')->setName('groblja.izmena');
    $this->post('/administracija/groblja/izmena', '\App\Controllers\GrobljaController:postGrobljaIzmena')->setName('groblja.izmena.post');
    // Transakcije
    $this->get('/kartoni/transakcije/{id}', '\App\Controllers\TransakcijeController:getTransakcijeKarton')->setName('kartoni.transakcije');
    $this->post('/kartoni/transakcije/razduzivanje/ajax', '\App\Controllers\TransakcijeController:ajaxRazduzivanje')->setName('transakcije.razduzivanje.ajax');
})->add(new AuthMiddleware($container));
