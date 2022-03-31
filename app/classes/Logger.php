<?php

namespace App\Classes;

use App\Models\Log;
use App\Models\Korisnik;

class Logger
{
    private $korisnik;
    private $model;
    const DODAVANJE = "dodavanje";
    const IZMENA = "izmena";
    const BRISANJE = "brisanje";
    const UPLOAD = "upload";

    public function __construct($korisnik)
    {
        $this->korisnik = $korisnik;
        $this->model = new Log();
    }

    public function log($tip, $model, $polje, $model_stari = null)
    {
        $tekst = '';
        if (is_array($polje)) {
            foreach ($polje as $p) {
                $tekst .= "{$p}: {$model->$p}, ";
            }
        } else {
            $tekst = "{$polje}: {$model->$polje}";
        }

        $data = [
            'opis' => "{$model->id}-{$model->getTable()}, {$tekst}",
            'tip' => $tip,
            'izmene' => json_encode($model_stari),
            'korisnik_id' => $this->korisnik->id,
        ];

        $this->model->insert($data);
        
    }
}
