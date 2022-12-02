<?php

namespace App\Jobs;

use App\Models\Pergunta;
use App\Services\AreasService;
use App\Services\PerguntasService;
use Exception;
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
    public function __construct( $pergunta, $area, $authToken)
    {
        $this->pergunta = $pergunta;
        $this->area = $area;
        $this->token = $authToken;
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
        sleep(1);
        $perguntaModel = new Pergunta();
        $pergunta = $perguntaModel->perguntaAreaTipo($this->pergunta);
        $spaceId = (new AreasService())->getOneAreaFromCommunity($this->area, $this->token);

        if(!$pergunta){
            $exception = new Exception("Question not found in database");
            $this->fail($exception);
        }

        $log = [
            "id" => $pergunta->id
        ];
        Log::channel('question')->info('Question migration started: ', $log);
        $accessToken = $this->token;
        $postTypeDiscussion = 'pFx8jaZAk22gnhS';

        $publishedAt = date("Y-m-d\TH:i:s\Z", strtotime($pergunta->datapergunta));

        //Setar espaço TESTE API provisoriamente
//        $spaceId = "Va2K5eBhIHTd";

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
        sleep(3);
        if($response->hasErrors()) {
            $log = [
                "message" => $response->getErrors(),
                "legalmaticId" => $pergunta->id
            ];

            Log::channel('question')->error('Failed to migrate question',$log);
            $exception = new Exception($response->getErrors()[0]['message']);
            $this->fail($exception);
        }
        else {
            $log = [
                "id" => $response->getData()['createPost']['id'],
                "legalmaticId" => $pergunta->id
            ];
            $pergunta->update([
                'idTribe' => $response->getData()['createPost']['id'],
                'migrado_em' => now()
                ]
            );
            Log::channel('question')->info('Question migration finished', $log);
            sleep(1);

        }
    }
}
