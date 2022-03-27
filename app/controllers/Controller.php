<?php

namespace App\Controllers;

class Controller {

	protected $container;

	const DODAVANJE = "dodavanje";
    const IZMENA = "izmena";
    const BRISANJE = "brisanje";
    const UPLOAD = "upload";

	function __construct($container) {
		$this->container = $container;
	}

	public function __get($property) {
		if($this->container->get($property)) {
			return $this->container->get($property);
		}
	}

	protected function render($response, $template, $vars = []) {
		$this->container->view->render($response, $template, $vars);
	}

	protected function log($tip, $model, $polje, $model_stari = null)
    {
        $this->logger->log($tip, $model, $polje, $model_stari);
    }
}
