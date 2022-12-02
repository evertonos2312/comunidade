<?php

namespace App\Jobs;

use App\Models\Pergunta;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Throwable;

class MigrateLote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $limit;
    public $area;
    public $token;
    public $perguntas;
    public $consultor;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Collection $perguntas ,$limit, $area, $token, $consultor)
    {
        $this->limit = $limit;
        $this->area = $area;
        $this->token = $token;
        $this->perguntas = $perguntas;
        $this->consultor = $consultor;
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
        $listOfAllJobs = [];
        $listAllReplies = [];

        foreach ($this->perguntas as $pergunta) {
            $job = new MigrateQuestion($pergunta->id, $this->area, $this->token, $this->consultor);
            $listOfAllJobs[] = $job;

            $jobReplies = new ReplyQuestion($pergunta->id, $this->token, $this->consultor);
            $listAllReplies[] = $jobReplies;

        }
        Bus::batch($listOfAllJobs)->name('Migrating Questions')->dispatch();
        sleep(5);
        Bus::batch($listAllReplies)->name('Replying Questions')->dispatch();
    }
}
