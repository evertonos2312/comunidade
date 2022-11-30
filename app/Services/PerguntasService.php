<?php

namespace App\Services;

use App\Models\Pergunta;
use App\Repositories\PerguntasRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Softonic\GraphQL\ClientBuilder;
use Illuminate\Support\Facades\Log;

class PerguntasService implements ShouldQueue
{
    protected $perguntaRepository;

    public function __construct(PerguntasRepository $perguntaRepository)
    {
        $this->perguntaRepository = $perguntaRepository;
    }

    public function getPergunta(string $pergunta)
    {
        return $this->perguntaRepository->getPerguntaComAreaTipo($pergunta);
    }

}
