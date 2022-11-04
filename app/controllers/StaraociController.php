<?php

namespace App\Controllers;

use App\Models\Staraoc;
use App\Models\Karton;

class StaraociController extends Controller
{
    public function getStaraoci($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $modelStaraoc = new Staraoc();
        $sql = "SELECT * FROM staraoci ORDER BY karton_id, redni_broj;";
        $staraoci = $modelStaraoc->paginate($page, $sql);

        $this->render($response, 'staraoci.twig', compact('staraoci'));
    }

    public function postStaraociPretraga($request, $response)
    {
        $_SESSION['DATA_STARAOCI_PRETRAGA'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('staraoci.pretraga'));
    }

    public function getStaraociPretraga($request, $response)
    {
        $data = $_SESSION['DATA_STARAOCI_PRETRAGA'];
        array_shift($data);
        array_shift($data);

        if (empty($data['jmbg']) && empty($data['prezime']) && empty($data['ime']) && empty($data['aktivan']) && empty($data['saldo'])) {
            return $this->getStaraoci($request, $response);
        }
        
        $data['jmbg'] = str_replace('%', '', $data['jmbg']);
        $data['prezime'] = str_replace('%', '', $data['prezime']);
        $data['ime'] = str_replace('%', '', $data['ime']);
        $data['aktivan'] = isset($data['aktivan']) ? 1 : 0;
        $data['saldo'] = isset($data['saldo']) ? 1 : 0;
        $jmbg = '%' . filter_var($data['jmbg'], FILTER_SANITIZE_STRING) . '%';
        $prezime = '%' . filter_var($data['prezime'], FILTER_SANITIZE_STRING) . '%';
        $ime = '%' . filter_var($data['ime'], FILTER_SANITIZE_STRING) . '%';
        $aktivan = $data['aktivan'];
        $saldo = $data['saldo'];
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
        if ($aktivan === 1) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "aktivan LIKE :aktivan";
            $params[':aktivan'] = $aktivan;
        }
        if ($saldo === 1) {
            if ($where !== " WHERE ") {
                $where .= " AND ";
            }
            $where .= "avans > :saldo";
            $params[':saldo'] = $saldo;
        }
        $where = $where === " WHERE " ? "" : $where;
        $model = new Staraoc();
        $sql = "SELECT * FROM {$model->getTable()}{$where} ORDER BY karton_id, redni_broj;";
        $staraoci = $model->paginate($page, $sql, $params);

        $this->render($response, 'staraoci.twig', compact('staraoci', 'data'));
    }

    public function getStaraociDodavanje($request, $response, $args)
    {
        $karton_id = $args['id'];
        $modelKarton = new Karton();
        $karton = $modelKarton->find($karton_id);
        $this->render($response, 'staraoc_dodavanje.twig', compact('karton_id', 'karton'));
    }

    public function postStaraociDodavanje($request, $response)
    {

		// ovde proveriti da li je nesto
		// dd('postStaraociDodavanje');

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
                'multi_unique' => 'staraoci.karton_id,redni_broj',
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
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja novog staraoca.');
            return $response->withRedirect($this->router->pathFor('staraoci.dodavanje', ['id' => $data['karton_id']]));
        } else {
            $aktivan = isset($data['aktivan']) ? 1 : 0;
            $data['aktivan'] = $aktivan;
            $model = new Staraoc();
            $model->insert($data);
            $id = $model->getLastId();
            $staraoc = $model->find($id);
            $this->log($this::DODAVANJE, $staraoc, ['jmbg', 'prezime', 'ime'], $staraoc);
            $this->flash->addMessage('success', 'Novi staraoc je uspešno upisan.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $data['karton_id']]));
        }
    }

    public function postStaraociBrisanje($request, $response)
    {
		$karton_id = (int)$request->getParam('modal_staraoc_karton_id');
		
		if($this->auth->user()->nivo !== 0)
		{
			$this->flash->addMessage('danger', "Samo administrator može da obriše staraoca.");
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $karton_id]));
		}

		$id = (int)$request->getParam('modal_staraoc_id');
        $modelStaraoc = new Staraoc();
        $staraoc = $modelStaraoc->find($id);
        $success = $modelStaraoc->deleteOne($id);
        if ($success) {
            $this->log($this::BRISANJE, $staraoc, ['jmbg', 'prezime', 'ime'], $staraoc);
            $this->flash->addMessage('success', "Staraoc je uspešno obrisan.");
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $karton_id]));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja staraoca.");
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $karton_id]));
        }
    }

    public function getStaraociIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelStaraoc = new Staraoc();
        $staraoc = $modelStaraoc->find($id);
        $this->render($response, 'staraoc_izmena.twig', compact('staraoc'));
    }

    public function getStaraociPregled($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelStaraoc = new Staraoc();
        $staraoc = $modelStaraoc->find($id);
        $this->render($response, 'staraoc_pregled.twig', compact('staraoc'));
    }

    public function postStaraociIzmena($request, $response)
    {

		// ovde proveriti da li je jedini aktivan sukorisnik
		// ako jeste ne dozvoliti promenu statusa aktivan
		// deljenje sa nulom ako nema njednog aktivnog staraoca
		// dd('postStaraociIzmena');

        $id = (int)$request->getParam('id');
        $id_kartona = (int) $request->getParam('karton_id');
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        unset($data['id']);
		unset($data['karton_id']);
        $validation_rules = [
            'redni_broj' => [
                'required' => true,
                'multi_unique' => 'staraoci.karton_id,redni_broj#id:' . $id,
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
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene dokumenta.');
            return $response->withRedirect($this->router->pathFor('staraoci.izmena', ['id' => $id_kartona]));
        } else {
            $modelStaraoc = new Staraoc();
            $staraoc_old = $modelStaraoc->find($id);
            $aktivan = isset($data['aktivan']) ? 1 : 0;
            $data['aktivan'] = $aktivan;
            $modelStaraoc->update($data, $id);
            $staraoc = $modelStaraoc->find($id);
            $this->log($this::IZMENA, $staraoc, ['jmbg', 'prezime', 'ime'], $staraoc_old);
            $this->flash->addMessage('success', 'Izmene staraoca su uspešno sačuvane.');
            return $response->withRedirect($this->router->pathFor('kartoni.pregled', ['id' => $id_kartona]));
        }
    }
}
