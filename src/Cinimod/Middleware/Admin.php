<?php

namespace Mkny\Cinimod\Middleware;

use Closure;

use Mkny\Cinimod\Logic;
// use Lavary\Menu;


class Admin
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
        echo (\Request::route()->getActionName());
        exit;

        // Build menu
        \Menu::make('MyNavBar', function($menu){
            $links = [];
            $links[] = array(
                'name' => 'System of a down',
                'link' => '',
                'route' => '',
                'belongs' => ''
                );
            $links[] = array(
                'name' => 'Generator',
                'link' => '/admin/g',
                'route' => 'adm::gen::index',
                'belongs' => 'System of a down',
                );
            // $a = collect($links);
            // ;
            // echo '<pre>';
            // print_r($a->where("belongs",'')->all());
            // exit('_var');

            // foreach ($links as $link) {
            //     $item = $menu;
            //     if($link['belongs']){
            //         // $item = 
            //         $menu->{camel_case($link['belongs'])}->add($link['name'], $link['route']?:$link['link']);
            //     } else {

            //     }
            // }
            // echo url()->current();exit;


            $menu->add('Matrix');
            $menu->matrix->add('Generator', array('route' => 'adm::gen::index'));
            $menu->matrix->add('Generator2', url('admin/g/config'));
            $menu->matrix->add('Generator2', 'www.google.com');
            // $menu->system->add('Configurator', array('route' => 'adm::config'));
            // dd($menu->get('system'));
        });
        // Build menu end
        
        return $next($request);
    }
}
