<?php

namespace Mkny\Cinimod\Middleware;

use Closure;

use Mkny\Cinimod\Logic;
// use Lavary\Menu;


class Admin extends DefaultMiddleware
{
    public function __construct()
    {
        
        Logic\UtilLogic::addViewVar('scripts', ['/js/cinimod.js']);
    }
}
