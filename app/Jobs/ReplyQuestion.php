<?php

namespace App\Jobs;

use Exception;
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
use Softonic\GraphQL\ClientBuilder;

class ReplyQuestion implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable;

    public $questionIdentify;
    public $token;
    public $consultor;
    public $tries = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($questionIdentify, $token, $consultor)
    {
        $this->questionIdentify = $questionIdentify;
        $this->token = $token;
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
        $log = [
            "id" => $this->questionIdentify
        ];
        $perguntaModel = new Pergunta();
        $pergunta = $perguntaModel->getPerguntaMigrada($this->questionIdentify);

        if(!isset($pergunta)){
            $this->fail();
        }
        $idTribe = $pergunta->idTribe;
        Log::channel('question')->info('Question reply started',$log);
        $accessToken = $this->token;
        $postTypeDiscussion = 'pFx8jaZAk22gnhS';

        $client = ClientBuilder::build(
            getenv('COMMUNITY_GRAPHQL'),
            [
                'headers' => ['Authorization' => "Bearer $accessToken"]
            ]
        );
        $replyText = json_encode($pergunta->resposta);
        $publishedAt = date("Y-m-d\TH:i:s\Z", strtotime($pergunta->datapergunta));

        $mutation = <<<'MUTATION'
            mutation ($postType: String!, $postId: ID!, $replyText: String!, $publishedAt: DateTime, $ownerId: ID){
              createReply(postId: $postId, input: {
                   postTypeId: $postType,
                   mappingFields: [
                   {
                        key: "title"
                        type: text
                        value: "\"\""
                   },
                   {
                        key: "content"
                        type: html
                        value: $replyText
                   }
                   ]
                    ownerId: $ownerId
                    createdAt: $publishedAt
                    publish: true
                })
            {
                id
            }
            }
            MUTATION;
        $variables = [
            'postType' => $postTypeDiscussion,
            'postId' => $idTribe,
            'replyText' => $replyText,
            "publishedAt" => $publishedAt,
            "ownerId" => $this->consultor
        ];

        $response = $client->query($mutation, $variables);

        if($response->hasErrors()) {
            $log = [
                "message" => $response->getErrors(),
                "question" => $idTribe
            ];
            Log::channel('question')->error('Failed to reply question',$log);
            $exception = new Exception($response->getErrors()[0]['message']);
            $this->fail($exception);
        }
        else {
            $log = [
                "id" => $response->getData()['createReply']['id'],
                "question" => $idTribe
            ];
            Log::channel('question')->info('Question replied finished', $log);
        }
    }
}
