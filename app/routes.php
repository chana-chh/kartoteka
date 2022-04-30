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
    $this->post('/raspored/ajax', '\App\Controllers\RasporedController:postRasporedAjax')->setName('raspored.ajax');
    $this->get('/raspored/stampa/{id}', '\App\Controllers\RasporedController:getRasporedStampa')->setName('raspored.stampa');
    $this->get('/raspored/danas', '\App\Controllers\RasporedController:getRasporedDanas')->setName('raspored.danas');
    $this->get('/raspored/tabela', '\App\Controllers\RasporedController:getRasporedTabela')->setName('raspored.tabela');
    //Mape
    $this->get('/mape', '\App\Controllers\MapeController:getMape')->setName('mape');
    $this->get('/kartoni/mapa/{id}', '\App\Controllers\KartoniController:getKartoniMapa')->setName('kartoni.mapa');
    $this->post('/kartoni/mapa/dodavanje', '\App\Controllers\KartoniController:postKartoniMapa')->setName('kartoni.mapa.dodavanje');
    $this->post('/mapa/dodavanje', '\App\Controllers\MapeController:postUpload')->setName('mapa.dodavanje');
    $this->post('/mapa/brisanje', '\App\Controllers\MapeController:postMapaBrisanje')->setName('mapa.brisanje');
    $this->get('/mapa/izmena/{id}', '\App\Controllers\MapeController:getMapaIzmena')->setName('mapa.izmena');
    $this->post('/mapa/izmena', '\App\Controllers\MapeController:postMapaIzmena')->setName('mapa.izmena.post');
    $this->get('/mapa/povezi/{id}', '\App\Controllers\MapeController:getMapaPovezi')->setName('mapa.povezi.get');
    $this->post('/mapa/povezi', '\App\Controllers\MapeController:postMapaPovezi')->setName('mapa.povezi.post');
    // Staraoci
    $this->get('/staraoci', '\App\Controllers\StaraociController:getStaraoci')->setName('staraoci');
    $this->get('/staraoci/pretraga', '\App\Controllers\StaraociController:getStaraociPretraga')->setName('staraoci.pretraga');
    $this->post('/staraoci/pretraga', '\App\Controllers\StaraociController:postStaraociPretraga');
    $this->get('/staraoci/dodavanje/{id}', '\App\Controllers\StaraociController:getStaraociDodavanje')->setName('staraoci.dodavanje');
    $this->post('/staraoci/dodavanje', '\App\Controllers\StaraociController:postStaraociDodavanje')->setName('staraoci.dodavanje.post');
    $this->post('/staraoci/brisanje', '\App\Controllers\StaraociController:postStaraociBrisanje')->setName('staraoci.brisanje');
    $this->get('/staraoci/izmena/{id}', '\App\Controllers\StaraociController:getStaraociIzmena')->setName('staraoci.izmena');
    $this->get('/staraoci/pregled/{id}', '\App\Controllers\StaraociController:getStaraociPregled')->setName('staraoci.pregled');
    $this->post('/staraoci/izmena', '\App\Controllers\StaraociController:postStaraociIzmena')->setName('staraoci.izmena.post');
    // Pokojnici
    $this->get('/pokojnici', '\App\Controllers\PokojniciController:getPokojnici')->setName('pokojnici');
    $this->get('/pokojnici/pretraga', '\App\Controllers\PokojniciController:getPokojniciPretraga')->setName('pokojnici.pretraga');
    $this->post('/pokojnici/pretraga', '\App\Controllers\PokojniciController:postPokojniciPretraga');
    $this->get('/pokojnici/dodavanje/{id}', '\App\Controllers\PokojniciController:getPokojniciDodavanje')->setName('pokojnici.dodavanje');
    $this->post('/pokojnici/dodavanje', '\App\Controllers\PokojniciController:postPokojniciDodavanje')->setName('pokojnici.dodavanje.post');
    $this->post('/pokojnici/brisanje', '\App\Controllers\PokojniciController:postPokojniciBrisanje')->setName('pokojnici.brisanje');
    $this->get('/pokojnici/izmena/{id}', '\App\Controllers\PokojniciController:getPokojniciIzmena')->setName('pokojnici.izmena');
    $this->get('/pokojnici/pregled/{id}', '\App\Controllers\PokojniciController:getPokojniciPregled')->setName('pokojnici.pregled');
    $this->post('/pokojnici/izmena', '\App\Controllers\PokojniciController:postPokojniciIzmena')->setName('pokojnici.izmena.post');
    // Logovi
    $this->get('/administracija/logovi', '\App\Controllers\LogController:getLog')->setName('logovi');
    $this->get('/administracija/logovi/pretraga', '\App\Controllers\LogController:getLogoviPretraga')->setName('logovi.pretraga');
    $this->post('/administracija/logovi/pretraga', '\App\Controllers\LogController:postLogoviPretraga');
    // Korisnici
    $this->get('/administracija/korisnici', '\App\Controllers\KorisniciController:getKorisnici')->setName('korisnici');
    $this->post('/administracija/korisnici/dodavanje', '\App\Controllers\KorisniciController:postKorisniciDodavanje')->setName('korisnici.dodavanje');
    $this->post('/administracija/korisnici/brisanje', '\App\Controllers\KorisniciController:postKorisniciBrisanje')->setName('korisnici.brisanje');
    $this->get('/administracija/korisnici/izmena/{id}', '\App\Controllers\KorisniciController:getKorisniciIzmena')->setName('korisnici.izmena');
    $this->post('/administracija/korisnici/izmena', '\App\Controllers\KorisniciController:postKorisniciIzmena')->setName('korisnici.izmena.post');
    // Groblja
    $this->get('/administracija/groblja', '\App\Controllers\GrobljaController:getGroblja')->setName('groblja');
    $this->post('/administracija/groblja/dodavanje', '\App\Controllers\GrobljaController:postGrobljaDodavanje')->setName('groblja.dodavanje');
    $this->post('/administracija/groblja/brisanje', '\App\Controllers\GrobljaController:postGrobljaBrisanje')->setName('groblja.brisanje');
    $this->get('/administracija/groblja/izmena/{id}', '\App\Controllers\GrobljaController:getGrobljaIzmena')->setName('groblja.izmena');
    $this->post('/administracija/groblja/izmena', '\App\Controllers\GrobljaController:postGrobljaIzmena')->setName('groblja.izmena.post');
    // Porezi
    $this->get('/porezi', '\App\Controllers\PoreziController:getPorezi')->setName('porezi');
    $this->get('/porez/dodavanje', '\App\Controllers\PoreziController:getPoreziDodavanje')->setName('porezi.dodavanje.get');
    $this->post('/porez/dodavanje', '\App\Controllers\PoreziController:postPoreziDodavanje')->setName('porezi.dodavanje.post');
    $this->get('/porez/izmena/{id}', '\App\Controllers\PoreziController:getPoreziIzmena')->setName('porezi.izmena');
    $this->post('/porez/izmena', '\App\Controllers\PoreziController:postPoreziIzmena')->setName('porezi.izmena.post');
    $this->post('/porez/brisanje', '\App\Controllers\PoreziController:postPoreziBrisanje')->setName('porezi.brisanje.post');
    // Artikli
    $this->get('/artikli', '\App\Controllers\ArtikliController:getArtikli')->setName('artikli');
    $this->get('/artikal/dodavanje', '\App\Controllers\ArtikliController:getArtikliDodavanje')->setName('artikli.dodavanje.get');
    $this->post('/artikal/dodavanje', '\App\Controllers\ArtikliController:postArtikliDodavanje')->setName('artikli.dodavanje.post');
    $this->get('/artikal/izmena/{id}', '\App\Controllers\ArtikliController:getArtikliIzmena')->setName('artikli.izmena');
    $this->post('/artikal/izmena', '\App\Controllers\ArtikliController:postArtikliIzmena')->setName('artikli.izmena.post');
    $this->post('/artikal/brisanje', '\App\Controllers\ArtikliController:postArtikliBrisanje')->setName('artikli.brisanje.post');
    // Transakcije Cene
    $this->get('/transakcije/cene', '\App\Controllers\CeneController:getCene')->setName('cene');
    $this->get('/transakcije/cene/dodavanje', '\App\Controllers\CeneController:getCeneDodavanje')->setName('cene.dodavanje.get');
    $this->post('/transakcije/cene/dodavanje', '\App\Controllers\CeneController:postCeneDodavanje')->setName('cene.dodavanje.post');
    $this->get('/transakcije/cene/izmena/{id}', '\App\Controllers\CeneController:getCeneIzmena')->setName('cene.izmena');
    $this->post('/transakcije/cene/izmena', '\App\Controllers\CeneController:postCeneIzmena')->setName('cene.izmena.post');
    $this->post('/transakcije/cene/brisanje', '\App\Controllers\CeneController:postCeneBrisanje')->setName('cene.brisanje.post');
    // Transakcije
    $this->get('/transakcije/zaduzivanje', '\App\Controllers\TransakcijeController:getZaduzivanje')->setName('transakcije.zaduzivanje');
    $this->post('/transakcije/zaduzivanje', '\App\Controllers\TransakcijeController:postZaduzivanje')->setName('transakcije.zaduzivanje.post');
    // $this->get('/transakcije/zaduzivanje/takse', '\App\Controllers\TransakcijeController:getZaduzivanjeTakse')->setName('transakcije.zaduzivanje.takse');
    // $this->post('/transakcije/zaduzivanje/takse', '\App\Controllers\TransakcijeController:postZaduzivanjeTakse')->setName('transakcije.zaduzivanje.takse.post');
    // $this->get('/transakcije/zaduzivanje/zakup', '\App\Controllers\TransakcijeController:getZaduzivanjeZakup')->setName('transakcije.zaduzivanje.zakup');
    // $this->post('/transakcije/zaduzivanje/zakup', '\App\Controllers\TransakcijeController:postZaduzivanjeZakup')->setName('transakcije.zaduzivanje.zakup.post');
    $this->get('/transakcije/opomene', '\App\Controllers\TransakcijeController:getOpomene')->setName('transakcije.opomene');
    $this->post('/transakcije/zaduzivanje/brisanje', '\App\Controllers\TransakcijeController:postZaduzenjeBrisanje')->setName('transakcije.zaduzivanje.brisanje');
    $this->post('/transakcije/sve/brisanje', '\App\Controllers\TransakcijeController:postSveBrisanje')->setName('sve.brisanje');
    $this->get('/transakcije/pregled/straoc/{id}[/{z}]', '\App\Controllers\TransakcijeController:getKartonPregled')->setName('transakcije.pregled');
    $this->get('/transakcije/pregled/stampa/staraoc/{id}', '\App\Controllers\TransakcijeController:getKartonPregledStampa')->setName('transakcije.pregled.stampa'); // stampa
    $this->get('/transakcije/karton/{id}', '\App\Controllers\TransakcijeController:getKartonRazduzivanje')->setName('transakcije.razduzivanje'); // razduzivanje
    $this->post('/transakcije/uplata', '\App\Controllers\TransakcijeController:postUplata')->setName('transakcije.uplata'); // post razduzivanje
    // Reprogrami
    $this->get('/transakcije/reprogrami/staraoc/{id}', '\App\Controllers\ReprogramiController:getKartonReprogrami')->setName('transakcije.reprogrami'); // reprogram
    $this->get('/transakcije/reprogram/{id}', '\App\Controllers\ReprogramiController:getReprogram')->setName('transakcije.reprogram');
    $this->get('/transakcije/reprogram/dodavanje/{id}', '\App\Controllers\ReprogramiController:getReprogramDodavanje')->setName('transakcije.reprogram.dodavanje');
    $this->get('/transakcije/reprogram/izmena/{id}', '\App\Controllers\ReprogramiController:getReprogramIzmena')->setName('transakcije.reprogram.izmena');
    $this->post('/transakcije/reprogram/dodavanje', '\App\Controllers\ReprogramiController:postReprogramDodavanje')->setName('transakcije.reprogram.dodavanje.post');
    $this->post('/transakcije/reprogram/izmena', '\App\Controllers\ReprogramiController:postReprogramIzmena')->setName('transakcije.reprogram.izmena.post');
    $this->post('/transakcije/reprogram/rata', '\App\Controllers\ReprogramiController:postReprogramUplataRate')->setName('transakcije.reprogram.uplata');
    // Racuni
    $this->get('/transakcije/racun/{id}', '\App\Controllers\RacuniController:getRacun')->setName('racun');
    $this->post('/transakcije/racun', '\App\Controllers\RacuniController:postRacun')->setName('racun.post');
    $this->post('/transakcije/racun/brisanje', '\App\Controllers\RacuniController:postRacunBrisanje')->setName('racun.brisanje');
    // Taksa
    $this->get('/transakcije/taksa/{id}', '\App\Controllers\TaksaController:getTaksa')->setName('taksa');
    $this->post('/transakcije/taksa', '\App\Controllers\TaksaController:postTaksa')->setName('taksa.post');
    // Zakup
    $this->get('/transakcije/zakup/{id}', '\App\Controllers\ZakupController:getZakup')->setName('zakup');
    $this->post('/transakcije/zakup', '\App\Controllers\ZakupController:postZakup')->setName('zakup.post');
    //Uplate
    $this->get('/transakcije/uplate/{id}', '\App\Controllers\UplataController:getUplate')->setName('uplate');
    $this->post('/transakcije/uplate/brisanje', '\App\Controllers\UplataController:postUplataBrisanje')->setName('uplate.brisanje');
    // Statistika
    $this->get('/statistika', '\App\Controllers\StatistikaController:getStatistika')->setName('statistika');

    // Razduzivanje viska para
    $this->post('/transakcije/visak', '\App\Controllers\UplataController:postVisak')->setName('transakcije.visak');
})->add(new AuthMiddleware($container));
