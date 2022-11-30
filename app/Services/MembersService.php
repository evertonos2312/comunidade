<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Softonic\GraphQL\ClientBuilder;

class MembersService
{

    public function getMemberFromCommunity($area)
    {
        $email = match ($area) {
            '1' => "comunidade.trabalhista@contmatic.com.br",
            '2' => "comunidade.previdenciaria@contmatic.com.br",
            '3' => "comunidade.tributaria@contmatic.com.br",
            '4' => "comunidade.contabil@contmatic.com.br",
            '5' => "comunidade.societaria@contmatic.com.br",
            default => 'comunidade.trabalhista@contmatic.com.br',
        };
        return Cache::remember("consultor_$email", 3600, function () use ($email) {
            $accessToken = session()->get('AUTH_USER')['token'];
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
            $areas = null;
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
