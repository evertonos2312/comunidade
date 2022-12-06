<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Softonic\GraphQL\ClientBuilder;

class MembersService
{

    public function getMemberFromCommunity($area, $token = null)
    {
       $email = '';
        switch ($area) {
            case '1':
                $email ="comunidade.trabalhista@contmatic.com.br";
                break;
            case '2':
                $email = "comunidade.previdenciaria@contmatic.com.br";
                break;
            case '3':
                $email =  "comunidade.tributaria@contmatic.com.br";
                break;
            case '4':
                $email = "comunidade.contabil@contmatic.com.br";
                break;
            case '5':
                $email = "comunidade.societaria@contmatic.com.br";
                break;
        }
        if(empty($email)){
            return false;
        }
        return Cache::remember("consultor_$email", 36000, function () use ($token, $email) {
            if($token){
                $accessToken = $token;
            } else {
                $accessToken = session()->get('AUTH_USER')['token'];
            }
            $client = ClientBuilder::build(
                getenv('COMMUNITY_GRAPHQL'),
                [
                    'headers' => ['Authorization' => "Bearer $accessToken"]
                ]
            );
            $query = <<<'QUERY'
             query post($email: String!)
                    {
                    members(limit: 1, query: $email) {
                            nodes {
                                status
                                username
                                email
                                id
                            }
                    }
                }
            QUERY;
            $variables = [
                'email' => $email,
            ];
            $response = $client->query($query, $variables);
            $areas = false;
            if($response->hasErrors()) {
                echo '<pre>';
                print_r($response->getErrors());
                echo '</pre>';
                die();
            }
            else {

                $areas = $response->getData()['members']['nodes'][0]['id'];

            }
            return $areas;
        });
    }
}
