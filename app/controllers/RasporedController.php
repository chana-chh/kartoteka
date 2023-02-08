<?php

namespace App\Controllers;

use App\Models\Raspored;
use App\Models\Karton;
use App\Models\Groblje;
use App\Models\Pokojnik;
use App\Models\Racun;
use App\Models\Uplata;
use App\Models\Log;
use App\Classes\Auth;
use DateTime;

class RasporedController extends Controller
{
	public function getRaspored($request, $response)
	{
		$modelRaspored = new Raspored();
		$dogadjajiA = $modelRaspored->all();

		$data = [];

        foreach ($dogadjajiA as $dog) {

            $data[] = (object) [
                "id" => $dog->id,
                "title" => [$dog->title],
                "start" => $dog->start,
                "end" => $dog->end,
                "description" => "Prijavio:".$dog->prezime_prijavioca. ' ' .$dog->ime_prijavioca
            ];
        }

        $dogadjaji = json_encode($data);
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

		$this->render($response, 'raspored_dodavanje.twig', compact('groblja'));
	}

	public function postRasporedDodavanje($request, $response)
	{
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);

		$validation_rules = [
            'prezime' => [
                'required' => true,
            ],
            'ime' => [
                'required' => true,
            ],
            'ime_prijavioca' => [
                'required' => true,
            ],
            'prezime_prijavioca' => [
                'required' => true,
            ],
            'start' => [
                'required' => true,
            ],
            'end' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);
        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom zakazivanja termina.');
            return $response->withRedirect($this->router->pathFor('pocetna'));
        } else {

            $prezime = filter_var($data['prezime'], FILTER_SANITIZE_STRING);
			$ime = filter_var($data['ime'], FILTER_SANITIZE_STRING);
			$jmbg = filter_var($data['jmbg'], FILTER_SANITIZE_STRING);
			$srednje_ime = filter_var($data['srednje_ime'], FILTER_SANITIZE_STRING);
			$mesto = filter_var($data['mesto'], FILTER_SANITIZE_STRING);
			$prebivaliste = filter_var($data['prebivaliste'], FILTER_SANITIZE_STRING);
			$datum_rodjenja = strlen($data['datum_rodjenja']) === 0 ? null : $data['datum_rodjenja'];
			$datum_smrti = strlen($data['datum_smrti']) === 0 ? null : $data['datum_smrti'];
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

			$tekst = "Nema podataka o groblju";
			if (!empty($data['groblje_id'])) {
				$id_groblja = (int)$data['groblje_id'];
        		$modelGroblja = new Groblje();
        		$groblje = $modelGroblja->find($id_groblja);
        		$tekst = $groblje->naziv;
			}
			
		$dstart = date("Y-m-d H:i:s", strtotime($data['start']));
        $modelRaspored = new Raspored();
        $sql = "SELECT * FROM raspored WHERE prezime = :prezime 
        AND ime = :ime
        AND ime_prijavioca = :ime_prijavioca
        AND prezime_prijavioca = :prezime_prijavioca
        AND groblje_id = {$data['groblje_id']}
        AND start = :dstart";
        $params[':prezime'] = $data['prezime'];
        $params[':ime'] = $data['ime'];
        $params[':ime_prijavioca'] = $data['ime_prijavioca'];
        $params[':prezime_prijavioca'] = $data['prezime_prijavioca'];
        $params[':dstart'] = $dstart;
		$postojeci = $modelRaspored->fetch($sql, $params);

		if (count($postojeci)>0) {
			$this->flash->addMessage('danger', 'Već postoji termin sa ovim podacima!');
            return $response->withRedirect($this->router->pathFor('raspored.dodavanje'));
		}

		$modelRaspored->insert([
			'start' => $data['start'],
			'end' => $data['end'],
			'title' => $tekst."  Pokojnik:".$ime." ".$prezime,
			'groblje_id' => $data['groblje_id'],
			'ime' => $ime,
			'prezime' => $prezime,
			'srednje_ime' => $srednje_ime,
			'jmbg' => $jmbg,
			'mesto' => $mesto,
			'prebivaliste' => $prebivaliste,
			'datum_rodjenja' => $datum_rodjenja,
			'datum_smrti' => $datum_smrti,
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
		$dataUrl['url'] = URL . "raspored/izmena/" . $id_rasporeda;
		$modelRaspored->update($dataUrl, $id_rasporeda);

		$raspored = $modelRaspored->find($id_rasporeda);
		$this->log($this::DODAVANJE, $raspored, ['ime', 'prezime', 'start', 'end'], $raspored);
		$this->flash->addMessage('success', "Zakazivanje termina je uspešno završeno.");
		return $response->withRedirect($this->router->pathFor('raspored'));
        }
	}

	public function getRasporedIzmena($request, $response, $args)
	{

		$id = (int)$args['id'];
		$modelRaspored = new Raspored();
		$raspored = $modelRaspored->find($id);

		$modelGroblje = new Groblje();
		$groblja = $modelGroblje->all();

		$this->render($response, 'raspored_izmena.twig', compact('raspored', 'groblja'));
	}	

	public function getRasporedPovezi($request, $response, $args)
	{

		$id = (int)$args['id'];
        $model = new Pokojnik();
        $pokojnik = $model->find($id);

		$modelRaspored = new Raspored();
		$rasporedi = $modelRaspored->povezivanje();

		$this->render($response, 'raspored_povezi.twig', compact('pokojnik','rasporedi'));
	}

	public function postRasporedPovezi($request, $response)
	{

		$id_pokojnika = (int)$request->getParam('pokojnik_id');
		$id_rasporeda = (int)$request->getParam('raspored_id');

		$modelPokojnik = new Pokojnik();
        $pokojnik = $modelPokojnik->find($id_pokojnika);

		$model = new Raspored();
		$raspored = $model->find($id_rasporeda);
		$data['pokojnik_id'] = $id_pokojnika;
		$data['karton_id'] = $pokojnik->karton_id;
		$data['groblje_id'] = $pokojnik->karton->groblje->id;
		$idkarton = (int)$data['karton_id'];
        $modelKartona = new Karton();
        $karton = $modelKartona->find($idkarton);
        $tekst = $karton->broj();
		$data['title'] = $tekst." ".$pokojnik->punoIme();

		$this->log($this::IZMENA, $raspored, ['title', 'start'], $raspored);
		$model->update($data, $id_rasporeda);
		$this->flash->addMessage('success', "Termin je su uspešno povezan sa pokojnikom i kartonom.");
		return $response->withRedirect($this->router->pathFor('raspored'));
	}

	public function getRasporedStampa($request, $response, $args)
	{

		$id = (int)$args['id'];
		$modelRaspored = new Raspored();
		$raspored = $modelRaspored->find($id);

		$this->render($response, 'raspored_stampa.twig', compact('raspored'));
	}

	public function getRasporedDanas($request, $response, $args)
	{

		$model = new Raspored();
		$danasnji = $model->danas();

		$this->render($response, 'print/raspored_danas.twig', compact('danasnji'));
	}

	public function postRasporedIzmena($request, $response)
	{

		$id = (int)$request->getParam('id');
		$data = $request->getParams();
		unset($data['csrf_name']);
		unset($data['csrf_value']);
		unset($data['id']);

		$pio = isset($data['pio']) ? 1 : 0;
		$data['pio'] = $pio;

		$validation_rules = [
            'prezime' => [
                'required' => true,
            ],
            'ime' => [
                'required' => true,
            ],
            'ime_prijavioca' => [
                'required' => true,
            ],
            'prezime_prijavioca' => [
                'required' => true,
            ],
            'start' => [
                'required' => true,
            ],
            'end' => [
                'required' => true,
            ],
        ];

        $this->validator->validate($data, $validation_rules);
        if ($this->validator->hasErrors()) {
            $this->flash->addMessage('danger', 'Došlo je do greške prilikom izmene termina.');
            return $response->withRedirect($this->router->pathFor('pocetna'));
        } else {
		$model = new Raspored();

		$raspored = $model->find($id);

		$this->log($this::IZMENA, $raspored, ['title', 'start'], $raspored);

		$model->update($data, $id);

		$this->flash->addMessage('success', "Podaci termina su uspešno izmenjeni.");
		return $response->withRedirect($this->router->pathFor('raspored'));
		}

	}

	public function postRasporedBrisanje($request, $response)
	{
		if ($this->auth->user()->nivo !== 0)
		{
			$this->flash->addMessage('success', "Samo administrator može da obriše termin.");
			return $response->withRedirect($this->router->pathFor('raspored'));
		}

		$id = (int)$request->getParam('termin_modal_id');
		$model = new Raspored();
		$raspored = $model->find($id);
		$success = $model->deleteOne($id);
		if ($success)
		{
			$this->log($this::BRISANJE, $raspored, ['title', 'start'], $raspored);
			$this->flash->addMessage('success', "Termin je uspešno obrisan.");
			return $response->withRedirect($this->router->pathFor('raspored'));
		}
		else
		{
			$this->flash->addMessage('danger', "Došlo je do greške prilikom brisanja termina.");
			return $response->withRedirect($this->router->pathFor('raspored'));
		}
	}

	public function postRasporedAjax($request, $response)
	{
		$data = $request->getParams();
		$parcela = '%' . filter_var($data['parcela'], FILTER_SANITIZE_STRING) . '%';
		$where = "groblje_id = :groblje_id AND parcela LIKE :parcela AND grobno_mesto = :grobno_mesto";
		$params = [':groblje_id' => $data['groblje_id'], ':parcela' => $parcela, ':grobno_mesto' => $data['grobno_mesto']];

		$model = new Karton();
		$sql = "SELECT * FROM {$model->getTable()} WHERE {$where} LIMIT 1;";
		$karton = $model->fetch($sql, $params);

		$poruka_staraoci = "";
		$i = 0;
		$poruka_pokojnici = "";
		$j = 0;
		$nov_karton = 0;
		if (!empty($karton))
		{
			$poruka = $karton[0]->broj();
			$staraoci = $karton[0]->staraoci();
			$pokojnici = $karton[0]->pokojnici();
			foreach ($staraoci as $staralac)
			{
				$i++;
				$poruka_staraoci .= $i . ". " . $staralac->ime . " " . $staralac->prezime;
			}
			foreach ($pokojnici as $pokojnik)
			{
				$j++;
				$poruka_pokojnici .= $j . ". " . $pokojnik->ime . " " . $pokojnik->prezime;
			}
		}
		else
		{
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

	public function postRasporedUkloni($request, $response)
	{
		
		$id = (int)$request->getParam('modal_raspored_id');

		$model = new Raspored();
		$data['karton_id'] = null;
		$data['pokojnik_id'] = null;

		$raspored = $model->find($id);
		$tekst = "Nema podataka o groblju";
			if ($raspored->groblje_id) {
				$id_groblja = (int)$raspored->groblje_id;
        		$modelGroblja = new Groblje();
        		$groblje = $modelGroblja->find($id_groblja);
        		$tekst = $groblje->naziv;
			}
		$data['title'] = $tekst;
		$success = $model->update($data, $id);
		if ($success)
		{
			$this->log($this::IZMENA, $raspored, ['title', 'start'], $raspored);
			$this->flash->addMessage('success', "Veza termina je uspešno raskinuta sa kartonom i pokojnikom.");
			return $response->withRedirect($this->router->pathFor('raspored'));
		}
		else
		{
			$this->flash->addMessage('danger', "Došlo je do greške prilikom raskidnja veze.");
			return $response->withRedirect($this->router->pathFor('raspored'));
		}
	}
}
