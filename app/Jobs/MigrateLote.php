<?php

namespace App\Jobs;

use App\Models\Pergunta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class MigrateLote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $limit;
    public $area;
    public $token;
    public $perguntas;
    public $consultor;

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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $listOfAllJobs = [];
        $listAllLegalmatic = [];
        $listAllReplies = [];

        foreach ($this->perguntas as $pergunta) {
            $job = new MigrateQuestion($pergunta->id, $this->area, $this->token, $this->consultor);
            $listOfAllJobs[] = $job;

            $jobUpdateLegalmatic = new UpdateQuestionLegalmatic($pergunta->id);
            $listAllLegalmatic[] = $jobUpdateLegalmatic;

            $jobReplies = new ReplyQuestion($pergunta->id, $this->token, $this->consultor);
            $listAllReplies[] = $jobReplies;

        }
        Bus::batch($listOfAllJobs)->name('Migrating Questions')->dispatch();
        Bus::batch($listAllLegalmatic)->name('Updating Questions in Legalmatic')->dispatch();
        Bus::batch($listAllReplies)->name('Replying Questions')->dispatch();
    }
}
