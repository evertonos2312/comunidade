<?php

namespace App\Jobs;

use App\Services\MembersService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class ReplyMigratedQuestions implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable;

    public $perguntas;
    public $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($perguntas, $token)
    {
        $this->perguntas = $perguntas;
        $this->token = $token;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new RateLimitedWithRedis('questions'))->dontRelease()];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $listAllReplies = [];
        foreach ($this->perguntas as $pergunta) {
            $consultor = (new MembersService())->getMemberFromCommunity($pergunta->area);
            $jobReplies = new ReplyQuestion($pergunta->id, $this->token, $consultor);
            $listAllReplies = $jobReplies;
        }
        Bus::batch($listAllReplies)->name('Retry Replying Questions')->dispatch();
    }
}
