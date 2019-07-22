<?php

namespace App\Controllers;

use App\Models\Raspored;
use App\Models\Karton;
use App\Models\Groblje;
use App\Models\Log;
use App\Classes\Auth;

class RasporedController extends Controller
{
    public function getRaspored($request, $response)
    {
        $modelRaspored = new Raspored();
        $dogadjajiA = $modelRaspored->all();
        $dogadjaji = json_encode($dogadjajiA);
        $this->render($response, 'raspored.twig', compact('dogadjaji'));
    }

    public function getRasporedDodavanje($request, $response, $args)
    {
        $modelGroblje = new Groblje();
        $groblja = $modelGroblje->all();

        $modelKarton = new Karton();
        $parcele = $modelKarton->vratiParcele();
        $kartoni = $modelKarton->all();

        $this->render($response, 'raspored_dodavanje.twig', compact('groblja', 'parcele', 'kartoni'));
    }

    public function postRasporedDodavanje($request, $response)
    {
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);

        if (!empty($data['groblje_id']) && !empty($data['parcela']) && !empty($data['grobno_mesto'])) {
            $where = "groblje_id = :groblje_id AND parcela = :parcela AND grobno_mesto = :grobno_mesto";
            $params = [':groblje_id' => $data['groblje_id' ], ':parcela' => $data['parcela' ], ':grobno_mesto' => $data['grobno_mesto' ]];

            $model = new Karton();
            $sql = "SELECT * FROM {$model->getTable()} WHERE {$where} LIMIT 1;";
            $karton = $model->fetch($sql, $params);
        }

        

        dd($karton);

        $modelRaspored = new Raspored();
        $modelRaspored->insert($data);
        $id_rasporeda = $modelRaspored->getLastId();

        $model = new Raspored();
        $data['url'] = URL."/raspored/izmena/". $id_rasporeda;
        $model->update($data, $id_rasporeda);
        
        $modelLog= new Log();
        $k = new Auth();
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

    public function postRasporedIzmena($request, $response){

        $id = (int)$request->getParam('id');
        $data = $request->getParams();
        unset($data['csrf_name']);
        unset($data['csrf_value']);
        unset($data['id']);

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
}
