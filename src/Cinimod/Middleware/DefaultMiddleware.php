<?php
// 2019
namespace Mkny\Cinimod\Middleware;

use Closure;
use DB;
use Cache;
use Illuminate\Http\Request;
use Route;

// use Mkny\Cinimod\Logic;
// use Lavary\Menu;


abstract class DefaultMiddleware
{

    protected $module;
    protected $action;
    protected $controller;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // Faz a definicao do ambiente atual
        $this->defineAmbient($request);

        // Monta os menus, settando a variavel na view diretamente
        app()->make('\Mkny\Cinimod\Logic\UtilLogic')->getMenus();

        // Joga pro proximo middleware
        return $next($request);
    }

    public function addVar(Request $request, Array $data)
    {

        $request->attributes->add($data);
    }

    /**
     * Define o ambiente da rota atual
     * 
     * @param  Request $request Requisicao
     * @return void
     */
    private function defineAmbient(Request $request)
    {
        // Endpoint da rota
        $endpoint = explode('@', ($request->route()->getActionName()));

        // mdd($request->route()->getActionName());

        // Define a localizacao do esquema
        $data = array();

        // Se o endpoint for Closure, indica que e uma acao de fim iminente
        if($endpoint[0] == 'Closure'){
            $data = array(
                'module' => $request->route()->getPrefix()?:'site',
                'controller' => 'SystemController',
                'action' => 'getClosure',
                'route' => $request->route()->getAction()
                );
        } else {
            $data = array(
                'module' => $request->route()->getPrefix()?:'site',
                'controller' => $endpoint[0],
                'action' => $endpoint[1],
                'route' => $request->route()->getAction()
                );
        }

        

        app()->make('\Mkny\Cinimod\Logic\UtilLogic')->addViewVar($data);

        // Setta na classe, para que caso as sub-classes precisem utilizar
        $this->module = $data['module'];
        $this->action = $data['action'];
        $this->controller = $data['controller'];

        // Adiciona as variaveis na requisicao
        $this->addVar($request, ['module' => $this->module]);
        $this->addVar($request, ['action' => $this->action]);
        $this->addVar($request, ['controller' => $this->controller]);
    }
}