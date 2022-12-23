<?php

namespace App\Jobs;

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

class Contmaker implements ShouldQueue, ShouldBeUnique
{
    use  Batchable,Dispatchable, InteractsWithQueue, Queueable;

    private $token;
    private $emailTo;
    private $nameTo;

    private $message;

    public $trie = 5;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $authToken, string $emailPara, string $nome, string $mensagem)
    {
        $this->token = $authToken;
        $this->emailTo = $emailPara;
        $this->nameTo = $nome;
        $this->message = $mensagem;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        sleep(1);
        $data= Date('Y-m-d\TH:i:s\Z', strtotime('+40 days'));
        $accessToken = $this->token;

        $client = ClientBuilder::build(
            getenv('COMMUNITY_GRAPHQL'),
            [
                'headers' => ['Authorization' => "Bearer $accessToken"]
            ]
        );
        $mutation = <<<'MUTATION'
            mutation ($email: String!, $name: String!, $message: String!, $expires: DateTime){
            inviteMembers(input: {
                    expiresAt: $expires
                    invitationMessage: $message
                    invitees: { email: $email, name: $name } }) {
                id
                status
                }
             }
            MUTATION;
        $variables = [
            'email' => $this->emailTo,
            'name' => $this->nameTo,
            'message' => $this->message,
            "expires" => $data,
        ];
        $response = $client->query($mutation, $variables);

        if($response->hasErrors()) {
            $log = [
                "message" => $response->getErrors(),
                "email" => $this->emailTo
            ];

            Log::channel('contmakers')->error('Failed to send email ',$log);
            $exception = new Exception($response->getErrors()[0]['message']);
            $this->fail($exception);
        } else {
            $log = [
                "id" => $response->getData()['createPost']['id'],
                "email" => $this->emailTo
            ];

            Log::channel('contmakers')->info('Email sended', $log);
        }
    }
}
