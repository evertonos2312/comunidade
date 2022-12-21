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
            ->where('status', 1)
            ->where('datapergunta', '>=', '2010-01-01 00:00:01')
            ->whereNot(function ($query) {
                $query->where('resposta', 'like', "%table%");
            })
            ->whereNot(function ($query) {
                $query->where('resposta', 'like', "%base64%");
            })
            ->where('perguntas.id', $pergunta)
            ->whereNotNull('resposta')
            ->whereRaw('resposta <> ""')
            ->whereRaw("char_length(resposta) <= 5000")
            ->firstOrFail();
    }

    public function getPerguntaMigrada($pergunta)
    {
        return Pergunta::where('id', $pergunta)
            ->whereNotNull('idTribe')
            ->where('status', 1)
            ->whereNotNull('resposta')
            ->whereRaw('resposta <> ""')
            ->where('resposta_tribe', NULL)
            ->whereRaw("char_length(resposta) <= 5000")
            ->firstOrFail();
    }

    public function getAllPerguntasMigradas()
    {
        return Pergunta::whereNotNull('idTribe')
            ->whereRaw('resposta <> ""')
            ->where('status', 1)
            ->where('resposta_tribe', NULL)
            ->whereNotNull('migrado_em')
            ->where('resposta_tribe', NULL)
            ->whereRaw("char_length(resposta) <= 5000")
            ->get();
    }

}
