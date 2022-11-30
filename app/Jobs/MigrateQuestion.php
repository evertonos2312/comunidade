<?php

namespace App\Jobs;

use App\Models\Pergunta;
use App\Services\PerguntasService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Softonic\GraphQL\ClientBuilder;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;

class MigrateQuestion implements ShouldQueue, ShouldBeUnique
{
    use  Batchable,Dispatchable, InteractsWithQueue, Queueable;

    private $pergunta;
    private $area;
    private $token;
    public $consultor;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 30;

    public $tries = 5;

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
    public function __construct( $pergunta, $area, $authToken, $consultor)
    {
        $this->pergunta = $pergunta;
        $this->area = $area;
        $this->token = $authToken;
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
        $perguntaModel = new Pergunta();
        $pergunta = $perguntaModel->perguntaAreaTipo($this->pergunta);
        if(!$pergunta){
            $this->fail();
        }

        $log = [
            "id" => $pergunta->id
        ];
        Log::channel('question')->info('Question migration started: ', $log);
        sleep(1);
        $accessToken = $this->token;
        $postTypeDiscussion = 'pFx8jaZAk22gnhS';

        $publishedAt = date("Y-m-d\TH:i:s\Z", strtotime($pergunta->datapergunta));

        //Setar espaço TESTE API provisoriamente
        $spaceId = "Y30gpnXAZ7Ql";

        if($pergunta->assunto == $pergunta->tipo){
            $tagNames = [
                $pergunta->assunto
            ];
        } else {
            $tagNames = [
                $pergunta->assunto,
                $pergunta->tipo
            ];
        }
        $title = json_encode($pergunta->pergunta);

        $client = ClientBuilder::build(
            getenv('COMMUNITY_GRAPHQL'),
            [
                'headers' => ['Authorization' => "Bearer $accessToken"]
            ]
        );
        $mutation = <<<'MUTATION'
            mutation ($postType: String!, $spaceId: ID!, $tags: [String!], $title: String!, $publishedAt: DateTime){
              createPost(
                spaceId: $spaceId,
                    input: {
                    postTypeId: $postType
                tagNames: $tags
                mappingFields: [
                    {
                        key: "title"
                        type: text
                        value: $title
                    },
                    {
                        key: "content"
                        type: html
                        value: "\"\""
                    }
                ]
                publishedAt: $publishedAt
                publish: true
            }
            )
            {
                id
            }
            }
            MUTATION;
        $variables = [
            'spaceId' => $spaceId,
            'postType' => $postTypeDiscussion,
            'tags' => $tagNames,
            "title" => $title,
            "publishedAt" => $publishedAt
        ];

        $response = $client->query($mutation, $variables);
        if($response->hasErrors()) {
            $log = [
                "message" => $response->getErrors(),
                "legalmaticId" => $pergunta->id
            ];

            Log::channel('question')->error('Failed to migrate question',$log);
            return null;
        }
        else {
            $log = [
                "id" => $response->getData()['createPost']['id'],
                "legalmaticId" => $pergunta->id
            ];
            $update = $pergunta->update(['idTribe' => $response->getData()['createPost']['id']]);
            sleep(1);
            Log::channel('question')->info('Question migration finished', $log);

        }
    }
}
