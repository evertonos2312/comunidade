<?php

namespace App\Repositories;


use App\Models\Pergunta;

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
        return $this->perguntaModel->where('id', $identify)->firstOrfail();
    }

}
