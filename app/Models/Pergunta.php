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
    ];

    public $timestamps = false;


    public function perguntaAreaTipo($pergunta)
    {
        return Pergunta::join('perguntasassunto', 'perguntas.idassunto', '=', 'perguntasassunto.id')
            ->join('perguntastipo', 'perguntas.idtipo', '=', 'perguntastipo.id')
            ->select('perguntas.*', 'perguntasassunto.titulo as assunto', 'perguntastipo.titulo as tipo')
            ->where('migrado_em', NULL)
            ->where('perguntas.id', $pergunta)
            ->whereNotNull('resposta')
            ->firstOrFail();
    }
}
