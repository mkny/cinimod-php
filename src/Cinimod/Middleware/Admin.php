<?php

namespace Mkny\Cinimod\Middleware;

use Closure;

use Mkny\Cinimod\Logic;
// use Lavary\Menu;


class Admin extends DefaultMiddleware
{

    protected function getAmbient()
    {
        $endpoint = explode('@', class_basename(\Request::route()->getActionName()));

        if($endpoint[0] == 'Closure'){
            return array(
                'module' => \Request::route()->getPrefix()?:'site',
                'controller' => 'SystemController',
                'action' => 'getClosure'
                );
        }
        return array(
            'module' => \Request::route()->getPrefix()?:'site',
            'controller' => $endpoint[0],
            'action' => $endpoint[1],
            );
    }

    public function init()
    {
        Logic\UtilLogic::addViewVar('scripts', ['/js/cinimod.js']);
        
    }
}
