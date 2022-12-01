<?php

namespace App\Jobs;

use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use App\Models\Pergunta;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateQuestionLegalmatic implements ShouldQueue, ShouldBeUnique
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 30;

    public $tries = 5;

    private $pergunta;

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->pergunta;
    }
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pergunta)
    {
        $this->pergunta = $pergunta;
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
        $log = [
            "id" => $this->pergunta
        ];
        Log::channel('question')->info('Question updated started in legalmatic', $log);
        $perguntaModel = new Pergunta();
        try {
            $pergunta = $perguntaModel->where('id', $this->pergunta)->firstOrfail();
        } catch (\Exception $exception) {
            $this->fail($exception);
        }

        $updated = $pergunta->update(['migrado_em' => now()]);
        if($updated){
            Log::channel('question')->info('Question updated finished in legalmatic', $log);

        } else {
            Log::channel('question')->error('Failed to update question in legalmatic',$log);
            $this->fail();
        }
    }
}
