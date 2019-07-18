<?php

namespace App\Controllers;

use App\Models\Raspored;
use App\Models\Log;

class LogController extends Controller
{
    public function getLog($request, $response)
    {
        $modelLog = new Log();
        $logovi = $modelLog->all();
        $this->render($response, 'log.twig', compact('logovi'));
    }
}
