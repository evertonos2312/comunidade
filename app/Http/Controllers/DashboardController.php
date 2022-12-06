<?php

namespace App\Http\Controllers;

use App\Services\PerguntasService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public $perguntasService;

    public function __construct(PerguntasService $perguntasService)
    {
        $this->perguntasService = $perguntasService;
    }

    public function show()
    {
        $totalPerguntas = $this->perguntasService->getTotalPerguntas();
        $totalPerguntasAno = $this->perguntasService->getTotalPerguntasAno();
        $anosPerguntas = [];
        $totalMigradas = 0;
        if(!empty($totalPerguntasAno)) {
            foreach ($totalPerguntasAno as $date) {
                $migradas = $this->perguntasService->getTotalMigradasPorAno($date->ano);
                $totalMigradas += $migradas;
                $percent = ($migradas /  $date->count) * 100;

                $atual = [
                    'ano' => $date->ano,
                    'total' => $date->count,
                    'migradas' => $migradas,
                    'percent' => $percent
                ];
                $anosPerguntas[] = $atual;
            }
        }
        $totalPercent = ($totalMigradas / $totalPerguntas) * 100;
        return view('dashboard', [
            'totalPerguntas' => $totalPerguntas,
            'migrados' => $anosPerguntas,
            'totalMigradas' => $totalMigradas,
            'totalPercent' => $totalPercent
        ]);
    }
}
