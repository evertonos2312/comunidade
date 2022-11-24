<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Softonic\GraphQL\ClientBuilder;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();
        $authUser = $this->runUserTokenQuery($this->only('email', 'password'));
        if (! $authUser) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function runUserTokenQuery($userAuth)
    {
        $accessToken = session()->get('GUEST_TOKEN');
        $client = ClientBuilder::build(
            getenv('COMMUNITY_GRAPHQL'),
            [
                'headers' => ['Authorization' => "Bearer $accessToken"]
            ]
        );
        $email = $userAuth['email'];
        $password = $userAuth['password'];
        $mutation = <<<'MUTATION'
            mutation ($email: String!, $password: String!){
              loginNetwork(
                input: { usernameOrEmail: $email, password: $password }
                ) {
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
            MUTATION;
        $variables = [
            'email' => $email,
            'password' => $password,
        ];

        $response = $client->query($mutation, $variables);
        if($response->hasErrors()) {
            return false;
        }
        else {
            $user = [
                'token' => $response->getData()['loginNetwork']['accessToken'],
                'name' => $response->getData()['loginNetwork']['member']['name'],
                'role' => $response->getData()['loginNetwork']['role']['name'],
                'idCommunity' => $response->getData()['loginNetwork']['member']['id']
            ];
            $this->createUserAuthSession($user);
            return true;
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }

    private function createUserAuthSession(array $user)
    {
        session()->put('AUTH_USER', $user);
    }
}
