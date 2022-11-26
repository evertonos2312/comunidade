<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerguntasRequest;
use App\Models\Pergunta;
use App\Repositories\PerguntasRepository;
use App\Services\PerguntasService;
use Softonic\GraphQL\ClientBuilder;

class MigrarPerguntaController extends Controller
{

    protected PerguntasService $perguntaService;

    public function __construct(PerguntasService $perguntaService)
    {
        $this->perguntaService = $perguntaService;
    }

    public function store(StorePerguntasRequest $request)
    {
        $validated = $request->validated();
        $pergunta = $this->perguntaService->getPergunta($validated['pergunta']);
        if($pergunta){
            $migrated = $this->perguntaService->storeQuestionInCommunity($pergunta, $validated['area']);
            redirect('log-viewer');

        }

    }


}
