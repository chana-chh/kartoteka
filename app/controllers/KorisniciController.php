<?php

namespace App\Controllers;

use App\Models\Korisnik;

class KorisniciController extends Controller
{
    public function getKorisnici($request, $response)
    {
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $page = isset($query['page']) ? (int)$query['page'] : 1;

        $model = new Korisnik();
        $sql = "SELECT * FROM {$model->getTable()} ORDER BY ime;";
        $korisnici = $model->paginate($page, $sql);

        $this->render($response, 'korisnici.twig', compact('korisnici'));
    }

    public function postKorisniciDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $validation_rules = [
            'ime' => [
                'required' => true,
                'minlen' => 5,
                'alnum' => true,
            ],
            'korisnicko_ime' => [
                'required' => true,
                'minlen' => 3,
                'maxlen' => 50,
                'alnum' => true,
                'unique' => 'korisnici.korisnicko_ime', // tabela.kolona
            ],
            'lozinka' => [
                'required' => true,
                'minlen' => 6,
            ],
            'lozinka_potvrda' => [
                'match_field' => 'lozinka',
            ],
            'nivo' => [
                'required' => true
            ],
        ];
        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom dodavanja korisnika.');
            return $response->withRedirect($this->router->pathFor('korisnici'));
        } else {
            $this->flash->addMessage('success', 'Novi korisnik je uspešno dodat.');
            $modelKorisnik = new Korisnik();
            unset($data['lozinka_potvrda']);
            $data['lozinka'] = password_hash($data['lozinka'], PASSWORD_BCRYPT);
            $modelKorisnik->insert($data);
            return $response->withRedirect($this->router->pathFor('korisnici'));
        }
    }

    public function postKorisniciBrisanje($request, $response)
    {
        $id = (int)$request->getParam('modal_korisnik_id');
        $modelKorisnika = new Korisnik();
        $success = $modelKorisnika->deleteOne($id);
        if ($success) {
            $this->flash->addMessage('success', "Korisnik je uspešno obrisan.");
            return $response->withRedirect($this->router->pathFor('korisnici'));
        } else {
            $this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja korisnika.");
            return $response->withRedirect($this->router->pathFor('korisnici'));
        }
    }

    public function getKorisniciIzmena($request, $response, $args)
    {
        $id = (int)$args['id'];
        $modelKorisnik = new Korisnik();
        $korisnik = $modelKorisnik->find($id);
        $this->render($response, 'korisnik_izmena.twig', compact('korisnik'));
    }

     public function postKorisniciIzmena($request, $response)
    {
        $data = $request->getParams();
        $id = $data['id'];
        unset($data['id']);
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        $validation_rules = [
            'ime' => [
                'required' => true,
                'minlen' => 5,
                'alnum' => true,
            ],
            'korisnicko_ime' => [
                'required' => true,
                'minlen' => 3,
                'maxlen' => 50,
                'alnum' => true,
                'unique' => 'korisnici.korisnicko_ime#id:' . $id,
            ],
            'nivo' => [
                'required' => true
            ],
        ];

        $validation_pass = [
            'lozinka' => [
                'required' => true,
                'minlen' => 6,
            ],
            'lozinka_potvrda' => [
                'match_field' => 'lozinka',
            ]
        ];

        if (!empty($data['lozinka'])) {
            array_push($validation_rules, $validation_pass);
        }

        $this->validator->validate($data, $validation_rules);

        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene podataka korisnika.');
            return $response->withRedirect($this->router->pathFor('korisnici.izmena', ['id' => $id]));
        } else {
            $this->flash->addMessage('success', 'Podaci o korisniku su uspešno izmenjeni.');
            $modelKorisnik = new Korisnik();
            unset($data['lozinka_potvrda']);
            if (!empty($data['lozinka'])) {
                $data['lozinka'] = password_hash($data['lozinka'], PASSWORD_BCRYPT);
            } else{
                unset($data['lozinka']);
            }
            $modelKorisnik->update($data, $id);
            return $response->withRedirect($this->router->pathFor('korisnici'));
        }
    }
}
