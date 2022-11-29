<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoteRequest;
use App\Http\Requests\StorePerguntasRequest;
use App\Jobs\MigrateLote;
use App\Jobs\MigrateQuestion;
use App\Jobs\ReplyQuestion;
use App\Jobs\StoreQuestion;
use App\Jobs\UpdateQuestionLegalmatic;
use App\Models\Pergunta;
use App\Repositories\PerguntasRepository;
use App\Services\PerguntasService;
use Illuminate\Support\Facades\Bus;
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
        $token = session()->get('AUTH_USER')['token'];
        MigrateQuestion::withChain([
            new UpdateQuestionLegalmatic($validated['pergunta']),
            new ReplyQuestion($validated['pergunta'], $token)
        ])->dispatch($validated['pergunta'], $validated['area'], $token);

       return redirect('log-viewer');
    }

    public function lote(StoreLoteRequest $request)
    {
        $validated = $request->validated();
        $token = session()->get('AUTH_USER')['token'];
        $area = $validated['area'];
        $perguntaModel = new Pergunta();
        $perguntas = $perguntaModel->where('migrado_em', NULL)
            ->whereNotNull('resposta')
            ->where('idTribe', NULL)
            ->whereNot( function ($query) {
                $query->where('resposta', 'like', "%table%");
            })->where('area',  $area)
            ->limit($validated['number'])
            ->get();

        MigrateLote::dispatch($perguntas ,$validated['number'], $area, $token);
        return redirect('horizon/dashboard');
    }
}
