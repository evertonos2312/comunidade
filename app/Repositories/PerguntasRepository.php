<?php

namespace App\Repositories;


use App\Models\Pergunta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerguntasRepository
{
    protected $perguntaModel;

    public function __construct(Pergunta $perguntaModel)
    {
        $this->perguntaModel = $perguntaModel;
    }


    public function getPerguntaComAreaTipo(string $identify)
    {
        return $this->perguntaModel->perguntaAreaTipo($identify);
    }

    public function updateMigrationPergunta(string $identify)
    {
        $pergunta = $this->getPerguntaById($identify);
        return  $pergunta->update(['migrado_em' => now()]);
    }

    public function getPerguntaById(string $identify)
    {
        return $this->perguntaModel->where('id', $identify)->where('status', '!=', 5)->firstOrfail();
    }

    public function getTotalPerguntasFromDatabase()
    {
        return Cache::remember("total_perguntas", 36000, function ()  {
           return DB::table('perguntas')->whereNot(function ($query) {
               $query->where('resposta', 'like', "%table%");
           })->whereRaw('resposta <> ""')->where('status', '!=', 5)->whereNotNull('resposta')->count();
        });
    }

    public function getTotalPerguntasAnoFromDatabase()
    {
        return Cache::remember("total_perguntas_anos", 86400, function ()  {
            return  $this->perguntaModel
                ->select(DB::raw('count(*) as count'),
                    DB::raw("DATE_FORMAT(datapergunta, '%Y') AS ano"))
                ->groupBy(DB::raw( "YEAR(datapergunta)"))
                ->orderByDesc('ano')
                ->where('status', '!=', 5)
                ->whereNotNull('resposta')
                ->whereRaw('resposta <> ""')
                ->whereNot(function ($query) {
                    $query->where('resposta', 'like', "%table%");
                })
                ->get();
        });
    }

    public function getTotalMigradasAnoFromDatabase (string $ano)
    {
        return Cache::remember("total_migrado_$ano", 3600, function () use ($ano) {
            return DB::table('perguntas')->whereRaw('resposta <> ""')->whereNotNull('resposta')->whereNot(function ($query) {
                $query->where('resposta', 'like', "%table%");
            })->whereNotNull('migrado_em')->where('status', '!=', 5)->whereNotNull('resposta_tribe')->whereYear('datapergunta', $ano)->count();
        });
    }
}
