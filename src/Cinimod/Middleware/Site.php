<?php

namespace Mkny\Cinimod\Middleware;

use Closure;

use Mkny\Cinimod\Logic;


class Site
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Logic\UtilLogic::addViewVar('scripts', ['/js/cinimod.js']);
        
        return $next($request);
    }
}
