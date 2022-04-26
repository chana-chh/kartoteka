<?php

namespace App\Controllers;

use App\Models\Cena;
use App\Models\Staraoc;
use App\Models\Zaduzenje;
use DateTime;

class ZakupController extends Controller
{

    public function getZakup($request, $response, $args)
    {
        $staraoc_id = (int) $args['id'];
        $staraoc = (new Staraoc())->find($staraoc_id);
        $cene = new Cena();

        $this->render($response, 'zakup.twig', compact('cene', 'staraoc'));
    }

    public function postZakup($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        
        $model = new Staraoc();
        $sql = "SELECT COUNT(*) AS broj FROM zaduzenja WHERE staraoc_id = :star AND godina = :god AND tip = 2;";
        $broj = $model->fetch($sql, [':god' => $data['godina'], ':star' => $data['staraoc_id']])[0]->broj;
        if ($broj > 0) {
            $this->flash->addMessage('danger', 'Već postoji zaduženje za odabranu godinu');
            return $response->withRedirect($this->router->pathFor('zakup', ['id' => $data['staraoc_id']]));
        } else {
            $staraoc = $model->find($data['staraoc_id']);
            $bm = $staraoc->karton()->broj_mesta;
            $bs = $staraoc->karton()->brojAktivnihStaraoca();

            $model_zaduzenje = new Zaduzenje();
            $model_zaduzenje->insert([
                'karton_id' => $staraoc->karton()->id,
                'staraoc_id' => $staraoc->id,
                'tip' => 'zakup',
                'godina' => (int) $data['godina'],
                'iznos_zaduzeno' => (float) ($data['iznos_zaduzeno'] * $bm / $bs),
                'iznos_razduzeno' => 0,
                'razduzeno' => 0,
                'datum_zaduzenja' =>$data['datum_zaduzenja'],
                'korisnik_id_zaduzio' => $this->auth->user()->id,
                'napomena' =>$data['napomena'],
            ]);

            $id = $model_zaduzenje->getLastId();
            $zazduzenje = $model_zaduzenje->find($id);
            $this->log($this::DODAVANJE, $zazduzenje, ['tip', 'godina'], $zazduzenje);
            $this->flash->addMessage('success', 'Staraoc je uspešno zadužen odgovarajućim zakupom.');
            return $response->withRedirect($this->router->pathFor('transakcije.pregled', ['id' => $data['staraoc_id']]));
        }
    }
}
