<?php

namespace Mkny\Cinimod\Middleware;

use Closure;

use Mkny\Cinimod\Logic;


class Site
{

    public function __construct()
    {
        parent::__construct();
        Logic\UtilLogic::addViewVar('scripts', ['/js/cinimod.js']);
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        return $next($request);
    }
}
