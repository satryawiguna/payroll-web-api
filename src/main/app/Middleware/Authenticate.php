<?php

namespace App\Middleware;

use App\Core\Auth\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{

    function handle($request, \Closure $next, ...$guards)
    {
        $token = $request->header('Authorization');
        if (!empty($token)) $token = substr($token, 7);
        // TODO: Bypass dummy token
        if ($token === 'dummy') {
            auth()->setUser(User::find(1));
            return $next($request);
        }
        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->is('api/*')) {
            return route('login');
        }
    }
}
