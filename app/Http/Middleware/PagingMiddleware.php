<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PagingMiddleware
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
        $pageSize = $req->query('page_size', 50);
        $req->pageSize = $pageSize > 100 ? 100 : ($pageSize < 1 ? 1 : $pageSize);

        $req->orderBy = $req->query('order_by');
        $orderAs = $req->query('order_as', 'asc');
        $req->orderAs = in_array($orderAs, ['asc', 'desc']) ? $orderAs : 'asc';

        $req->isPaginated = true;

        return $next($req);
    }
}
