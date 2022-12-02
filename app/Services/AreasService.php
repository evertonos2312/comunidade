<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Softonic\GraphQL\ClientBuilder;

class AreasService
{

    public function getAreasFromCommunity()
    {
        return Cache::remember('spacesTribe', 3600, function () {
            $accessToken = session()->get('AUTH_USER')['token'];
            $client = ClientBuilder::build(
                getenv('COMMUNITY_GRAPHQL'),
                [
                    'headers' => ['Authorization' => "Bearer $accessToken"]
                ]
            );
            $query = <<<'QUERY'
                {
                    spaces(limit: 30) {
                        nodes {
                            id
                            type
                            slug
                            name
                        }
                    }
                }
            QUERY;

            $response = $client->query($query);
            $areas = null;
            if($response->hasErrors()) {
                echo '<pre>';
                print_r($response->getErrors());
                echo '</pre>';
                die();
            }
            else {
                // Returns an array with all the data returned by the GraphQL server.
                $areas = $response->getData()['spaces']['nodes'];

            }
            return $areas;
        });
    }

    public function getOneAreaFromCommunity($area , $token)
    {
        $areaNome = match ($area) {
            '1' => "Trabalhista",
            '2' => "Previdenciaria",
            '3' => "Tributaria",
            '4' => "Contabil",
            '5' => "Societaria",
            default => 'Trabalhista',
        };

        return Cache::remember("area_$areaNome", 7200, function () use ($token, $areaNome) {
            $accessToken = $token;
            $client = ClientBuilder::build(
                getenv('COMMUNITY_GRAPHQL'),
                [
                    'headers' => ['Authorization' => "Bearer $accessToken"]
                ]
            );
            $query = <<<'QUERY'
            query post($areaNome: String!)
                {
                    spaces(limit: 1 query: $areaNome) {
                        nodes {
                            id
                            type
                            slug
                            name
                        }
                    }
                }
            QUERY;

            $variables = [
                'areaNome' => $areaNome
            ];

            $response = $client->query($query, $variables);
            $area = "ZKMXMAxDxTeO";
            if($response->hasErrors()) {
                echo '<pre>';
                print_r($response->getErrors());
                echo '</pre>';
                die();
            }
            else {
                $area = $response->getData()['spaces']['nodes'][0]['id'];
            }
            return $area;
        });
    }
}
