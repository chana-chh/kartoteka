<?php

namespace App\Controllers;

use App\Models\Pokojnik;
use App\Models\Karton;

class PokojniciController extends Controller
{
    public function getPokojnici($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Pokojnik();
        $sql = "SELECT * FROM {$model->getTable()} ORDER BY karton_id, redni_broj;";
        $pokojnici = $model->paginate($page, $sql);

        $this->render($response, 'pokojnici.twig', compact('pokojnici'));
    }

    public function postPokojniciPretraga($request, $response)
    {
        $_SESSION['DATA_POKOJNICI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('pokojnici.pretraga'));
    }

    public function getPokojniciPretraga($request, $response)
    {
        $data = $_SESSION['DATA_POKOJNICI_PRETRAGA'];
        array_shift($data);
        array_shift($data);
        if (empty($data['jmbg']) && empty($data['prezime']) && empty($data['ime']) && empty($data['datum_smrti']) && empty($data['datum_sahrane'])) {
            return $this->getPokojnici($request, $response);
        }
        $data['jmbg'] = str_replace('%', '', $data['jmbg']);
        $data['prezime'] = str_replace('%', '', $data['prezime']);
        $data['ime'] = str_replace('%', '', $data['ime']);
        $jmbg = '%' . filter_var($data['jmbg'], FILTER_SANITIZE_STRING) . '%';
        $prezime = '%' . filter_var($data['prezime'], FILTER_SANITIZE_STRING) . '%';
        $ime = '%' . filter_var($data['ime'], FILTER_SANITIZE_STRING) . '%';
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $where = " WHERE ";
        $params = [];
        if (!empty($data['jmbg'])) {
            $where .= "jmbg LIKE :jmbg";
            $params[':jmbg'] = $jmbg;
        }
        if (!empty($data['prezime'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "prezime LIKE :prezime";
            $params[':prezime'] = $prezime;
        }
        if (!empty($data['ime'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "ime LIKE :ime";
            $params[':ime'] = $ime;
        }
        if (!empty($data['datum_smrti'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "datum_smrti = :datum_smrti";
            $params[':datum_smrti'] = $data['datum_smrti'];
        }
        if (!empty($data['datum_sahrane'])) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "datum_sahrane = :datum_sahrane";
            $params[':datum_sahrane'] = $data['datum_sahrane'];
        }
        $where = $where === " WHERE " ? "" : $where;
        $model = new Pokojnik();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY karton_id, redni_broj;";
        $pokojnici = $model->paginate($page, $sql, $params);

        $this->render($response, 'pokojnici.twig', compact('pokojnici', 'data'));
    }

    public function getPokojniciDodavanje($request, $response, $args)
    {
        $karton_id = $args['id'];
        $modelKarton = new Karton();
        $karton = $modelKarton->find($karton_id);
        $this->render($response, 'pokojnik_dodavanje.twig', compact('karton_id', 'karton'));
    }

    public function postPokojniciDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        $validation_rules = [
            'karton_id' => [
                'required' => true,
                'min' => 1,
            ],
            'redni_broj' => [
                'required' => true,
                'multi_unique' => 'pokojnici.karton_id,redni_broj',
            ],
            'prezime' => [
                'required' => true,
            ],
            'ime' => [
                'required' => true,
            ],
            'jmbg' => [
                'required' => true,
                'jmbg' => true,
            ],
        ];
        $this->validator->validate($data, $validation_rules);
        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja novog pokojnika.');
            return $response->withRedirect($this->router->pathFor('pokojnici.dodavanje', ['id' => $data['karton_id']]));
        } else {
            $dupla_raka = isset($data['dupla_raka']) ? 1 : 0;
            $data['dupla_raka'] = $dupla_raka;
            $data['datum_rodjenja'] = strlen($data['datum_rodjenja']) === 0 ? null : $data['datum_rodjenja'];
            $data['datum_smrti'] = strlen($data['datum_smrti']) === 0 ? null : $data['datum_smrti'];
            $data['datum_sahrane'] = strlen($data['datum_sahrane']) === 0 ? null : $data['datum_sahrane'];
            $data['datum_ekshumacije'] = strlen($data['datum_ekshumacije']) === 0 ? null : $data['datum_ekshumacije'];
            $model = new Pokojnik();
            $model->insert($data);
            $id = $model->getLastId();
            $pokojnik = $model->find($id);
            $this->log($this::DODAVANJE, $pokojnik, ['ime', 'prezime'], $pokojnik);
            $this->flash->addMessage('success', 'Novi pokojnik je uspešno upisan.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $data['karton_id']]));
        }
    }

    public function postPokojniciBrisanje($request, $response)
    {
		$karton_id = (int)$request->getParam('modal_pokojnik_karton_id');

		if($this->auth->user()->nivo !== 0)
		{
			$this->flash->addMessage('danger', "Samo administrator može da obriše pokojnika.");
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $karton_id]));
		}

        $id = (int)$request->getParam('modal_pokojnik_id');
        $model = new Pokojnik();
        $pokojnik = $model->find($id);
        $success = $model->deleteOne($id);
        if ($success) {
            $this->log($this::BRISANJE, $pokojnik, ['ime', 'prezime'], $pokojnik);
            $this->flash->addMessage('success', "Pokojnik je uspešno obrisan.");
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $karton_id]));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja pokojnika.");
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $karton_id]));
        }
    }

    public function getPokojniciPregled($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelPokojnik = new Pokojnik();
        $pokojnik = $modelPokojnik->find($id);
        $this->render($response, 'pokojnik_pregled.twig', compact('pokojnik'));
    }

    public function getPokojniciIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $model = new Pokojnik();
        $pokojnik = $model->find($id);
        $this->render($response, 'pokojnik_izmena.twig', compact('pokojnik'));
    }

    public function postPokojniciIzmena($request, $response)
    {
        $id = (int)$request->getParam('id');
        $id_kartona = (int)$request->getParam('karton_id');
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        unset($data['id']);
        $validation_rules = [
            'redni_broj' => [
                'required' => true,
                'multi_unique' => 'pokojnici.karton_id,redni_broj#id:' . $id,
            ],
            'prezime' => [
                'required' => true,
            ],
            'ime' => [
                'required' => true,
            ],
            'jmbg' => [
                'required' => true,
                'jmbg' => true,
            ],
        ];
        $this->validator->validate($data, $validation_rules);
        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene pokojnika.');
            return $response->withRedirect($this->router->pathFor('pokojnici.izmena', ['id' => $id_kartona]));
        } else {
            $model = new Pokojnik();
            $stari = $model->find($id);
            unset($data['karton_id']);
            $dupla_raka = isset($data['dupla_raka']) ? 1 : 0;
            $data['dupla_raka'] = $dupla_raka;
            $data['datum_rodjenja'] = strlen($data['datum_rodjenja']) === 0 ? null : $data['datum_rodjenja'];
            $data['datum_smrti'] = strlen($data['datum_smrti']) === 0 ? null : $data['datum_smrti'];
            $data['datum_sahrane'] = strlen($data['datum_sahrane']) === 0 ? null : $data['datum_sahrane'];
            $data['datum_ekshumacije'] = strlen($data['datum_ekshumacije']) === 0 ? null : $data['datum_ekshumacije'];
            $model->update($data, $id);
            $pokojnik = $model->find($id);
            $this->log($this::IZMENA, $pokojnik, ['ime', 'prezime'], $stari);
            $this->flash->addMessage('success', 'Izmene pokojnika su uspešno sačuvane.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
    }
}
