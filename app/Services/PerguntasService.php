<?php

namespace App\Services;

use App\Models\Pergunta;
use App\Repositories\PerguntasRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Softonic\GraphQL\ClientBuilder;
use Illuminate\Support\Facades\Log;

class PerguntasService
{
    protected $perguntaRepository;

    public function __construct(PerguntasRepository $perguntaRepository)
    {
        $this->perguntaRepository = $perguntaRepository;
    }

    public function getPergunta(string $pergunta)
    {
        return $this->perguntaRepository->getPerguntaComAreaTipo($pergunta);
    }

    public function storeQuestionInCommunity(Pergunta $pergunta, string $spaceId)
    {
        $accessToken = session()->get('AUTH_USER')['token'];
        $postTypeDiscussion = 'pFx8jaZAk22gnhS';

        $publishedAt = date("Y-m-d\TH:i:s\Z", strtotime($pergunta->datapergunta));

        //Setar espaço TESTE API provisoriamente
        $spaceId = "uDRrIKgHH2SQ";

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
            Log::channel('question')->info('Question migrated', $log);
           return ['question' => $response->getData()['createPost']['id'], 'reply' => $pergunta->resposta, 'publishedAt' => $publishedAt];
        }
    }

    public function updatePergunta(string $identify)
    {
        $log = [
            "id" => $identify
        ];
        $updated = $this->perguntaRepository->updateMigrationPergunta($identify);
        if($updated){
            Log::channel('question')->info('Question updated in legalmatic', $log);
            return true;
        } else {
            Log::channel('question')->error('Failed to update question in legalmatic',$log);
            return false;
        }
    }

    public function storeReplyPostToQuestion($questionIdentify, $replyText, $publishedAt)
    {
        $accessToken = session()->get('AUTH_USER')['token'];
        $postTypeDiscussion = 'pFx8jaZAk22gnhS';
        $client = ClientBuilder::build(
            getenv('COMMUNITY_GRAPHQL'),
            [
                'headers' => ['Authorization' => "Bearer $accessToken"]
            ]
        );
        $replyText = json_encode($replyText);

        $mutation = <<<'MUTATION'
            mutation ($postType: String!, $postId: ID!, $replyText: String!, $publishedAt: DateTime){
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
            publishedAt: $publishedAt
            publish: true
                })
            {
                id
            }
            }
            MUTATION;
        $variables = [
            'postType' => $postTypeDiscussion,
            'postId' => $questionIdentify,
            'replyText' => $replyText,
            "publishedAt" => $publishedAt
        ];

        $response = $client->query($mutation, $variables);

        if($response->hasErrors()) {
            $log = [
                "message" => $response->getErrors(),
                "question" => $questionIdentify
            ];
            Log::channel('question')->error('Failed to reply question',$log);
            return false;
        }
        else {
            $log = [
                "id" => $response->getData()['createReply']['id'],
                "question" => $questionIdentify
            ];
            Log::channel('question')->info('Question replied successfully', $log);
            return true;
        }
    }
}
