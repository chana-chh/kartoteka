<?php

namespace App\Controllers;

use App\Models\Raspored;
use App\Models\Karton;
use App\Models\Groblje;
use App\Models\Pokojnik;
use App\Models\Racun;
use App\Models\Log;
use App\Classes\Auth;
use DateTime;

class RasporedController extends Controller
{
    public function getRaspored($request, $response)
    {
        $modelRaspored = new Raspored();
        $dogadjajiA = $modelRaspored->all();
        $dogadjaji = json_encode($dogadjajiA);
        $this->render($response, 'raspored.twig', compact('dogadjaji'));
    }

    public function getRasporedTabela($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $modelRaspored = new Raspored();
        $termini = $modelRaspored->paginate($page);

        $this->render($response, 'raspored_tabela.twig', compact('termini'));
    }

    public function getRasporedDodavanje($request, $response, $args)
    {
        $modelGroblje = new Groblje();
        $groblja = $modelGroblje->all();

        $modelKarton = new Karton();
        $tipovi = $modelKarton->enumOrSetList('tip_groba');

        $this->render($response, 'raspored_dodavanje.twig', compact('groblja', 'tipovi'));
    }

    public function postRasporedDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        //Za sada ako su popunjena sva tri polja, koja će biti obavezna. Nigde nema validacije.

        if (!empty($data['groblje_id']) && !empty($data['parcela']) && !empty($data['grobno_mesto'])) {
            $parcela = '%' . filter_var($data['parcela'], FILTER_SANITIZE_STRING) . '%';
            $where = "groblje_id = :groblje_id AND parcela LIKE :parcela AND grobno_mesto = :grobno_mesto";
            $params = [':groblje_id' => $data['groblje_id' ], ':parcela' => $parcela, ':grobno_mesto' => $data['grobno_mesto' ]];

            $model = new Karton();
            $sql = "SELECT * FROM {$model->getTable()} WHERE {$where} LIMIT 1;";
            $karton = $model->fetch($sql, $params);
            if(!empty($karton)){
                $id_kartona = $karton[0]->id;
            }else{
                $modelKartona = new Karton();
                $karton = $modelKartona->insert(
                [
                    'groblje_id' => $data['groblje_id'],
                    'parcela' => $data['parcela'],
                    'grobno_mesto' => $data['grobno_mesto'],
                    'broj_mesta' => 1,
                    'aktivan' => 1,
                    'tip_groba' => $data['tip_groba']
                ]
                );

                $id_kartona = $modelKartona->getLastId();
            }
        }

            $modelKartonTitle = new Karton();
            $karton_title = $modelKartonTitle->find($id_kartona);
            $redni_broj = count($karton_title->pokojnici()) + 1;

            $dupla_raka = isset($data['dupla_raka']) ? 1 : 0;
            $jmbg = filter_var($data['jmbg'], FILTER_SANITIZE_STRING);
            $prezime = filter_var($data['prezime'], FILTER_SANITIZE_STRING);
            $ime = filter_var($data['ime'], FILTER_SANITIZE_STRING);
            $srednje_ime = filter_var($data['srednje_ime'], FILTER_SANITIZE_STRING);
            $mesto = filter_var($data['mesto'], FILTER_SANITIZE_STRING);
            $prebivaliste = filter_var($data['prebivaliste'], FILTER_SANITIZE_STRING);
            $datum_rodjenja = strlen($data['datum_rodjenja']) === 0 ? null : $data['datum_rodjenja'];
            $datum_smrti = strlen($data['datum_smrti']) === 0 ? null : $data['datum_smrti'];
            $datum_ekshumacije = null;

            $model_Pokojnika = new Pokojnik();
            $pokojnik = $model_Pokojnika->insert(
                [
                    'karton_id' => $id_kartona,
                    'redni_broj' => $redni_broj,
                    'prezime' => $prezime,
                    'ime' => $ime,
                    'srednje_ime' => $srednje_ime,
                    'jmbg' => $jmbg,
                    'mesto' => $mesto,
                    'prebivaliste' => $prebivaliste,
                    'dupla_raka' => $dupla_raka,
                    'datum_rodjenja' => $datum_rodjenja,
                    'datum_smrti' => $datum_smrti,
                    'datum_sahrane' => $data['start'],
                ]
                );

            $id_pokojnika = $model_Pokojnika->getLastId();

        $prezime_prijavioca = filter_var($data['prezime_prijavioca'], FILTER_SANITIZE_STRING);
        $ime_prijavioca = filter_var($data['ime_prijavioca'], FILTER_SANITIZE_STRING);
        $prezime_troskovi = filter_var($data['prezime_troskovi'], FILTER_SANITIZE_STRING);
        $ime_troskovi = filter_var($data['ime_troskovi'], FILTER_SANITIZE_STRING);
        $jmbg_troskovi = filter_var($data['jmbg_troskovi'], FILTER_SANITIZE_STRING);
        $prebivaliste_troskovi = filter_var($data['prebivaliste_troskovi'], FILTER_SANITIZE_STRING);
        $ovlascen = filter_var($data['ovlascen'], FILTER_SANITIZE_STRING);
        $mup = filter_var($data['mup'], FILTER_SANITIZE_STRING);
        $telefon = filter_var($data['telefon'], FILTER_SANITIZE_STRING);

        $pio = isset($data['pio']) ? 1 : 0;

        $modelRaspored = new Raspored();
        $raspored = $modelRaspored->insert([
                    'start' => $data['start'],
                    'end' => $data['end'],
                    'title' => $karton_title->broj().", ".$ime." ".$prezime,
                    'karton_id' => $id_kartona,
                    'pokojnik_id' => $id_pokojnika,
                    'broj_lk' => $data['broj_lk'],
                    'prezime_prijavioca' => $prezime_prijavioca,
                    'ime_prijavioca' => $ime_prijavioca,
                    'prezime_troskovi' => $prezime_troskovi,
                    'ime_troskovi' => $ime_troskovi,
                    'jmbg_troskovi' => $jmbg_troskovi,
                    'prebivaliste_troskovi' => $prebivaliste_troskovi,
                    'ovlascen' => $ovlascen,
                    'mup' => $mup,
                    'telefon' => $telefon,
                    'uplata_do' => $data['uplata_do'],
                    'datum_prijave' => $data['datum_prijave'],
                    'pio' => $pio,
                    'napomena' => $data['napomena'],
                    'prevoz' => $data['prevoz']
                ]);
        $id_rasporeda = $modelRaspored->getLastId();

        $modelR = new Raspored();
        $dataU['url'] = URL."/raspored/izmena/". $id_rasporeda;
        $modelR->update($dataU, $id_rasporeda);

        $modelLog= new Log();
        $k = new Auth();
        $idzaracun = $k->user()->id;

        if (!empty($data['broj']) && !empty($data['datum']) && !empty($data['iznos'])) {
            
        $razduzeno = isset($data['razduzeno']) ? 1 : 0;

        $modelRacun = new Racun();
        if($razduzeno == 1){
            $racun = $modelRacun->insert([
                    'karton_id' => $id_kartona,
                    'broj' => $data['broj'],
                    'datum' => $data['datum'],
                    'iznos' => $data['iznos'],
                    'napomena' => $data['napomena_racun'],
                    'razduzeno' => $razduzeno,
                    'datum_razduzenja' => $data['datum_razduzenja'],
                    'korisnik_id_zaduzio' => $idzaracun,
                    'korisnik_id_razduzio' => $idzaracun,
                    'rok' => $data['uplata_do']
                ]);}else{
                $racun = $modelRacun->insert([
                    'karton_id' => $id_kartona,
                    'broj' => $data['broj'],
                    'datum' => $data['datum'],
                    'iznos' => $data['iznos'],
                    'napomena' => $data['napomena_racun'],
                    'razduzeno' => $razduzeno,
                    'datum_razduzenja' => $data['datum_razduzenja'],
                    'korisnik_id_zaduzio' => $idzaracun,
                    'rok' => $data['uplata_do']
                ]);
            }
        }

        $l = $k->user()->ime;
        $modelLog->insert([opis => $l."je dodao termin za sahranu sa id brojem ".$id_rasporeda ]);

        $this->flash->addMessage('success', "Zakazivanje termina je uspešno završeno.");
        return $response->withRedirect($this->router->pathFor('raspored'));
    }

    public function getRasporedIzmena($request, $response, $args){

        $id = (int)$args['id'];
        $modelRaspored = new Raspored();
        $raspored = $modelRaspored->find($id);

        $this->render($response, 'raspored_izmena.twig', compact('raspored'));
    }

    public function getRasporedStampa($request, $response, $args){

        $id = (int)$args['id'];
        $modelRaspored = new Raspored();
        $raspored = $modelRaspored->find($id);

        $this->render($response, 'raspored_stampa.twig', compact('raspored'));
    }

    public function postRasporedIzmena($request, $response){

        $id = (int)$request->getParam('id');
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        unset($data['id']);

        $pio = isset($data['pio']) ? 1 : 0;
        $data['pio'] = $pio;

        $model = new Raspored();
        $model->update($data, $id);

        $this->flash->addMessage('success', "Podaci termina su uspešno izmenjeni.");
        return $response->withRedirect($this->router->pathFor('raspored'));
    }

    public function postRasporedBrisanje($request, $response)
    {
        $id = (int)$request->getParam('modal_id');
        $model = new Raspored();
        $success = $model->deleteOne($id);
        if ($success) {
            $this->flash->addMessage('success', "Termin je uspešno obrisan.");
            return $response->withRedirect($this->router->pathFor('raspored'));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja termina.");
            return $response->withRedirect($this->router->pathFor('raspored'));
        }
    }

    public function postRasporedAjax($request, $response)
    {
            $data = $request->getParams();
            $parcela = '%' . filter_var($data['parcela'], FILTER_SANITIZE_STRING) . '%';
            $where = "groblje_id = :groblje_id AND parcela LIKE :parcela AND grobno_mesto = :grobno_mesto";
            $params = [':groblje_id' => $data['groblje_id' ], ':parcela' => $parcela, ':grobno_mesto' => $data['grobno_mesto' ]];

            $model = new Karton();
            $sql = "SELECT * FROM {$model->getTable()} WHERE {$where} LIMIT 1;";
            $karton = $model->fetch($sql, $params);

            $poruka_staraoci ="";
            $i = 0;
            $poruka_pokojnici ="";
            $j = 0;
            $nov_karton = 0;
            if(!empty($karton)){
                $poruka = $karton[0]->broj();
                $staraoci = $karton[0]->staraoci();
                $pokojnici = $karton[0]->pokojnici();
                foreach ($staraoci as $staralac) {
                    $i++;
                    $poruka_staraoci .= $i.". ".$staralac->ime." ".$staralac->prezime;
                }
                foreach ($pokojnici as $pokojnik) {
                    $j++;
                    $poruka_pokojnici .= $j.". ".$pokojnik->ime." ".$pokojnik->prezime;
                }
            }else{
                $poruka = "Obzirom da ne postoji karton sa ovim parametrima biće kreiran novi!";
                $nov_karton = 1;
            }

        $rezultat = [];
        $rezultat['csrf_name'] = $this->csrf->getTokenName();
        $rezultat['csrf_value'] = $this->csrf->getTokenValue();
        $rezultat['poruka'] = $poruka;
        $rezultat['poruka_staraoci'] = $poruka_staraoci;
        $rezultat['poruka_pokojnici'] = $poruka_pokojnici;
        $rezultat['nov_karton'] = $nov_karton;
        return json_encode($rezultat);
    }
}
