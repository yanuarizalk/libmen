<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $req, Closure $next)
    {
        $res = $next($req);
        return $res;
    }
}
