<?php

namespace App\Models;

use App\Classes\Model;

class Mapa extends Model
{
	protected $table = 'mape';

	public function karton()
	{
		return $this->belongsTo('App\Models\Karton', 'karton_id');
	}

	public function groblje()
	{
		return $this->belongsTo('App\Models\Groblje', 'groblje_id');
	}

	/**
     * Pronalazi mapu na osnovu Groblja i parcele
     *
     * @param $id_groblja i naziv parcele
     * @return \App\Classes\Model\Mapa
     */
    public function pronadjiMapu(int $id_groblja, string $parecela_naziv)
    {
        $sql = "SELECT * FROM {$this->table} WHERE groblje_id = :id_groblja AND parcela LIKE :parecela_naziv LIMIT 1;";
        $params = [":id_groblja" => $id_groblja, ":parecela_naziv" => '%'.$parecela_naziv.'%'];
        return $this->fetch($sql, $params) === [] ? null : $this->fetch($sql, $params)[0];
    }


    /**
     * Pronalazi mape sa istom vezom
     *
     * @param naziv mape odnosno ime slike
     * @return \App\Classes\Model\Mapa
     */
    public function isteMape(string $veza_naziv)
    {
        $sql = "SELECT * FROM {$this->table} WHERE veza LIKE :veza_naziv;";
        $params = [":veza_naziv" => '%'.$veza_naziv.'%'];
        return $this->fetch($sql, $params) === [] ? null : $this->fetch($sql, $params);
    }
}
