<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Softonic\GraphQL\ClientBuilder;

class AreasService
{

    public function getAreasFromCommunity()
    {
        return Cache::remember('areas', 3600, function () {
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
}
