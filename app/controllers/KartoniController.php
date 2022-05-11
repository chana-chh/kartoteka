<?php

namespace App\Controllers;

use App\Models\Karton;
use App\Models\Groblje;
use App\Models\Mapa;

class KartoniController extends Controller
{
    public function getKartoni($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $modelKarton = new Karton();
        $modelGroblje = new Groblje();
        $kartoni = $modelKarton->paginate($page);
        $groblja = $modelGroblje->all();

        $this->render($response, 'kartoni.twig', compact('kartoni', 'groblja'));
    }

    public function getKartoniDodavanje($request, $response)
    {
        $modelGroblje = new Groblje();
        $modelKarton = new Karton();
        $groblja = $modelGroblje->all();
        $tipovi = $modelKarton->enumOrSetList('tip_groba');

        $this->render($response, 'karton_dodavanje.twig', compact('groblja', 'tipovi'));
    }

    public function postKartoniDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        unset($data['stanje']);

        $validation_rules = [
            'groblje_id' => [
                'required' => true,
                'multi_unique' => 'kartoni.groblje_id,parcela,grobno_mesto',
            ],
            'parcela' => [
                'required' => true,
            ],
            'grobno_mesto' => [
                'required' => true,
            ],
            'broj_mesta' => [
                'required' => true,
                'min' => 1,
            ],
            'tip_groba' => [
                'required' => true,
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom upisivanja novog kartona.');
            return $response->withRedirect($this->router->pathFor('kartoni.dodavanje'));
        } else {
            $aktivan = isset($data['aktivan']) ? 1 : 0;
            $data['aktivan'] = $aktivan;
            $modelKarton = new Karton();
            $modelKarton->insert($data);
            $id = $modelKarton->getLastId();
            $karton = $modelKarton->find($id);
            $this->log($this::DODAVANJE, $karton, ['groblje_id', 'parcela', 'grobno_mesto'], $karton);
            $this->flash->addMessage('success', 'Novi karton je uspešno upisan.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id]));
        }
    }

    public function getKartoniIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelGroblje = new Groblje();
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id);
        $groblja = $modelGroblje->all();
        $tipovi = $modelKarton->enumOrSetList('tip_groba');
        $this->render($response, 'karton_izmena.twig', compact('karton', 'groblja', 'tipovi'));
    }

    public function postKartoniIzmena($request, $response)
    {
        $data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        unset($data['stanje']);


        $validation_rules = [
            'groblje_id' => [
                'required' => true,
                'multi_unique' => 'kartoni.groblje_id,parcela,grobno_mesto#id:' . $id,
            ],
            'parcela' => [
                'required' => true,
            ],
            'grobno_mesto' => [
                'required' => true,
            ],
            'broj_mesta' => [
                'required' => true,
                'min' => 1,
            ],
            'tip_groba' => [
                'required' => true,
            ]
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene podataka u kartonu.');
            return $response->withRedirect($this->router->pathFor('kartoni.izmena', ['id' => $id]));
        } else {
            $aktivan = isset($data['aktivan']) ? 1 : 0;
            $data['aktivan'] = $aktivan;
            $modelKarton = new Karton();
            $stari = $modelKarton->find($id);
            $broj = $stari->broj();
            $modelKarton->update($data, $id);
            $karton = $modelKarton->find($id);
            $this->log($this::IZMENA, $karton, $broj, $stari);
            $this->flash->addMessage('success', 'Izmene u kartonu su uspešno upisane.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id]));
        }
    }

    public function postKartoniBrisanje($request, $response)
    {

		// nema brisanja kartona samo prelazi u neaktivan
		// samo u slucaju greske moze da brise admin
		
		dd('postKartoniBrisanje');

        $id = (int)$request->getParam('brisanje_id');
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id);
        $broj = $karton->broj();
        if(!$karton->rasporedi() && !$karton->staraoci() && !$karton->pokojnici() && !$karton->dokumenti()){
            $success = $modelKarton->deleteOne($id);
        if ($success) {
                $this->log($this::BRISANJE, $karton, $broj, $karton);
                $this->flash->addMessage('success', "Karton broj [{$broj}] je uspešno obrisan.");
                return $response->withRedirect($this->router->pathFor('kartoni'));
            } else {
                $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja kartona.");
                return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id]));
            }
        } else {
                $this->flash->addMessage('danger', "Pre brisanja kartona neophodno je obrisati svu skeniranu dokumentaciju, staraoce, pokojnike, transakcije i termine vezane za njega.");
                return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id]));
        }
    }

    public function postKartoniPretraga($request, $response)
    {
        $_SESSION['DATA_KARTONI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('kartoni.pretraga'));
    }

    public function getKartoniPretraga($request, $response)
    {
        $data = $_SESSION['DATA_KARTONI_PRETRAGA'];
        array_shift($data);
        array_shift($data);
        
        if (!isset($data['groblje_id']) && empty($data['parcela']) && empty($data['grobno_mesto']) && !isset($data['aktivan'])) {
            return $this->getKartoni($request, $response);
        }
        $groblje_id = isset($data['groblje_id']) ? (int) str_replace('%', '', filter_var($data['groblje_id'], FILTER_SANITIZE_STRING)): 0;
        $data['parcela'] = str_replace('%', '', filter_var($data['parcela'], FILTER_SANITIZE_STRING));
        $data['grobno_mesto'] = str_replace('%', '', filter_var($data['grobno_mesto'], FILTER_SANITIZE_STRING));
        $parcela = '%' . $data['parcela'] . '%';
        $grobno_mesto = '%' . $data['grobno_mesto'] . '%';
        $aktivan = isset($data['aktivan']) ? 1 : 0;
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = " WHERE ";
        $params = [];
        if ($groblje_id !== 0) {
            $where .= "groblje_id = :groblje_id";
            $params[':groblje_id'] = $groblje_id;
        }
        if (!empty($data['parcela'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "parcela LIKE :parcela";
            $params[':parcela'] = $parcela;
        }
        if (!empty($data['grobno_mesto'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "grobno_mesto LIKE :grobno_mesto";
            $params[':grobno_mesto'] = $grobno_mesto;
        }
        if ($aktivan === 1) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "aktivan = :aktivan";
            $params[':aktivan'] = $aktivan;
        }
        $where = $where === " WHERE " ? "" : $where;

        $modelGroblje = new Groblje();
        $groblja = $modelGroblje->all();

        $model = new Karton();
        $sql = "SELECT * FROM {$model->getTable()} {$where};";

        $kartoni = $model->paginate($page, $sql, $params);

        $this->render($response, 'kartoni.twig', compact('kartoni', 'groblja', 'data'));
    }

    public function getKartoniPregled($request, $response, $args)
    {
        $id = $args['id'];
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id);
        $termini = $karton->rasporedi();
        $saldo = $karton->saldo();
        $this->render($response, 'karton_pregled.twig', compact('karton', 'saldo', 'termini'));
    }

    public function getKartoniMapa($request, $response, $args)
    {
        $id = $args['id'];
        $mapa = null;
        $sirina = 0;
        $visina = 0;
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id);
        $grobno_mesto = $karton->grobno_mesto;
        $modelMapa = new Mapa();
        $mapaM = $modelMapa->pronadjiMapu($karton->groblje_id, $karton->parcela);
        if ($mapaM) {
        $mapa = (string)($mapaM->veza);
        $slika = 'img/Mape/'.$mapa;
        list($sirina, $visina) = getimagesize($slika);
        }

        $this->render($response, 'karton_mapa.twig', compact('karton', 'grobno_mesto', 'mapa', 'sirina', 'visina'));
    }

    public function postKartoniMapa($request, $response)
    {
        $modelKarton = new Karton();
        $id_kartona = $request->getParam('id_kartona');
        $karton = $modelKarton->update(
            [
                'x_pozicija' => $request->getParam('x_pozicija'),
                'y_pozicija' => $request->getParam('y_pozicija')
            ],
            $id_kartona
        );

        $this->flash->addMessage('success', 'Koordinate za mapu su uspesno dodati/izmenjene.');
        return $response->withRedirect($this->router->pathFor('kartoni.mapa', ['id' => $id_kartona]));
    }
}
