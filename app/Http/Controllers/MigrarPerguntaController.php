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
use App\Services\MembersService;
use App\Services\PerguntasService;
use Illuminate\Support\Facades\Bus;
use Softonic\GraphQL\ClientBuilder;

class MigrarPerguntaController extends Controller
{

    protected PerguntasService $perguntaService;
    protected MembersService $membersService;

    public function __construct(PerguntasService $perguntaService, MembersService $membersService)
    {
        $this->perguntaService = $perguntaService;
        $this->membersService = $membersService;
    }

    public function store(StorePerguntasRequest $request)
    {
        $validated = $request->validated();
        $token = session()->get('AUTH_USER')['token'];
        $consultor = $this->membersService->getMemberFromCommunity($validated['areaLegalmatic']);
        MigrateQuestion::withChain([
            new ReplyQuestion($validated['pergunta'], $token, $consultor)
        ])->dispatch($validated['pergunta'], $validated['area'], $token);

       return redirect('horizon/dashboard');
    }

    public function lote(StoreLoteRequest $request)
    {
        $validated = $request->validated();
        $token = session()->get('AUTH_USER')['token'];
        $area = $validated['area'];
        $consultor = $this->membersService->getMemberFromCommunity($area);

        $perguntaModel = new Pergunta();
        $perguntas = $perguntaModel->where('migrado_em', NULL)
            ->whereNotNull('resposta')
            ->where('idTribe', NULL)
            ->whereNot( function ($query) {
                $query->where('resposta', 'like', "%table%");
            })->where('area',  $area)
            ->limit($validated['number'])
            ->get();

        MigrateLote::dispatch($perguntas ,$validated['number'], $area, $token, $consultor);
        return redirect('horizon/batches');
    }
}
