<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pergunta extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'migrado_em',
        'idTribe',
        'resposta_tribe'
    ];

    public $timestamps = false;




    public function perguntaAreaTipo($pergunta)
    {
        return Pergunta::join('perguntasassunto', 'perguntas.idassunto', '=', 'perguntasassunto.id')
            ->join('perguntastipo', 'perguntas.idtipo', '=', 'perguntastipo.id')
            ->select('perguntas.*', 'perguntasassunto.titulo as assunto', 'perguntastipo.titulo as tipo')
            ->where('migrado_em', NULL)
            ->whereNot(function ($query) {
                $query->where('resposta', 'like', "%table%");
            })
            ->where('perguntas.id', $pergunta)
            ->whereNotNull('resposta')
            ->whereRaw('resposta <> ""')
            ->firstOrFail();
    }

    public function getPerguntaMigrada($pergunta)
    {
        return Pergunta::where('id', $pergunta)
            ->whereNotNull('idTribe')
            ->whereNotNull('resposta')
            ->whereRaw('resposta <> ""')
            ->where('resposta_tribe', NULL)
            ->firstOrFail();
    }

    public function getAllPerguntasMigradas()
    {
        return Pergunta::whereNotNull('idTribe')
            ->whereRaw('resposta <> ""')
            ->where('resposta_tribe', NULL)
            ->whereNotNull('migrado_em')
            ->where('resposta_tribe', NULL)
            ->get();
    }

}
