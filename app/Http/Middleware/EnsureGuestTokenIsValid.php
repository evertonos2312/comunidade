<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Softonic\GraphQL\ClientBuilder;

class EnsureGuestTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!session()->has('GUEST_TOKEN')){
            $guestQueryIsValid = $this->runGuestQuery();
            if(!$guestQueryIsValid){
                return redirect()->to('/');
            }
        }
        return $next($request);
    }

    private function runGuestQuery(): bool
    {
        $client = ClientBuilder::build(getenv('COMMUNITY_GRAPHQL'));
        $contCommunity = getenv('CONT_COMMUNITY');

        $query = <<<'QUERY'
        query ($contCommunity: String) {
                tokens(networkDomain: $contCommunity) {
                accessToken
                role {
                    name
                    scopes
                    }
                member {
                    id
                    name
                }
            }
        }
        QUERY;

        $variables = [
            'contCommunity' => $contCommunity,
        ];

        $response = $client->query($query, $variables);
        if($response->hasErrors()) {
            echo '<pre>';
            print_r($response->getErrors());
            echo '</pre>';
            die();
            return false;
        }
        else {
            // Returns an array with all the data returned by the GraphQL server.
            $this->createGuestSession($response->getData()['tokens']['accessToken']);
            return true;
        }
    }

    private function createGuestSession(string $token)
    {
        session()->put('GUEST_TOKEN', $token);
    }
}
