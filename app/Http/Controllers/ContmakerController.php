<?php

namespace App\Http\Controllers;

use App\Jobs\Contmaker;
use App\Services\ContmakerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class ContmakerController extends Controller
{

    protected $contmakerService;
    public function __construct(ContmakerService $contmakerService)
    {
        $this->middleware('inviter');
        $this->contmakerService = $contmakerService;
    }

    public function index()
    {
        $token = session()->get('AUTH_USER')['token'];
        $membros = $this->contmakerService->getMembros();
        $mensagem = $this->contmakerService->getMensagem();

        $listAllJobs = [];
        foreach ($membros as $membro) {
            $job = new Contmaker($token, $membro['email'], $membro['nome'], $mensagem);
            $listAllJobs = $job;
        }
        Bus::batch($listAllJobs)->name('Sending Emails')->dispatch();
        return redirect('horizon/batches');
    }
}
