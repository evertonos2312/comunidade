<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EnsureAuthUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return string
     */
    public function handle(Request $request, Closure $next)
    {
        if(!session()->has('AUTH_USER')){
            return route('login');
        }
        $authUser = session()->get('AUTH_USER');
        View::composer('*', static function ($view) use ($authUser) {
            $view->with('user', $authUser);
        });
        return $next($request);
    }
}
