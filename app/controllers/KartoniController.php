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
            ],
            'stanje' => [
                'required' => true,
                'min' => 0,
            ],
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
            $this->flash->addMessage('success', 'Novi karton je uspešno upisan.');
            return $response->withRedirect($this->router->pathFor('kartoni'));
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
            ],
            'stanje' => [
                'required' => true,
                'min' => 0,
            ],
        ];

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene podataka u kartonu.');
            return $response->withRedirect($this->router->pathFor('kartoni.izmena', ['id' => $id]));
        } else {
            $aktivan = isset($data['aktivan']) ? 1 : 0;
            $data['aktivan'] = $aktivan;
            $modelKarton = new Karton();
            $modelKarton->update($data, $id);
            $this->flash->addMessage('success', 'Izmene u kartonu su uspešno upisane.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id]));
        }
    }

    public function postKartoniBrisanje($request, $response)
    {
        $id = (int)$request->getParam('brisanje_id');
        $modelKarton = new Karton();
        $broj = $modelKarton->find($id)->broj();
        $success = $modelKarton->deleteOne($id);
        if ($success) {
            $this->flash->addMessage('success', "Karton broj [{$broj}] je uspešno obrisan.");
            return $response->withRedirect($this->router->pathFor('kartoni'));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja kartona.");
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
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = "groblje_id = :groblje_id";
        $params = [':groblje_id' => $data['groblje_id']];
        if (!empty($data['parcela'])) {
            $where .= " AND parcela = :parcela";
            $params[':parcela'] = $data['parcela'];
        }
        if (!empty($data['grobno_mesto'])) {
            $where .= " AND grobno_mesto = :grobno_mesto";
            $params[':grobno_mesto'] = $data['grobno_mesto'];
        }
        $modelGroblje = new Groblje();
        $groblja = $modelGroblje->all();

        $model = new Karton();
        $sql = "SELECT * FROM {$model->getTable()} WHERE {$where};";
        $kartoni = $model->paginate($page, $sql, $params);

        $this->render($response, 'kartoni.twig', compact('kartoni', 'groblja', 'data'));
    }

    public function getKartoniPregled($request, $response, $args)
    {
        $id = $args['id'];
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id);
        $saldo = $karton->saldo();
        $this->render($response, 'karton_pregled.twig', compact('karton', 'saldo'));
    }

    public function getKartoniMapa($request, $response, $args)
    {
        $id = $args['id'];
        $modelKarton = new Karton();
        $karton = $modelKarton->find($id);
        $grobno_mesto = $karton->grobno_mesto;
        $modelMapa = new Mapa();
        $mapaM = $modelMapa->pronadjiMapu($karton->groblje_id, $karton->parcela);
        $mapa = (string)($mapaM->veza);
        $this->render($response, 'karton_mapa.twig', compact('karton', 'grobno_mesto', 'mapa'));
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
