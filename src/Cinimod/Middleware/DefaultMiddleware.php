<?php

namespace Mkny\Cinimod\Middleware;

use Closure;
use DB;
use Cache;

use Mkny\Cinimod\Logic;
// use Lavary\Menu;


abstract class DefaultMiddleware
{
	protected $module;
	protected $action;
	protected $controller;

	abstract protected function getAmbient();

	private function setAmbient()
	{
		$data = $this->getAmbient();
		$this->module = $data['module'];
		$this->action = $data['action'];
		$this->controller = $data['controller'];

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
    	$this->init();

    	$this->getMenus();

    	return $next($request);
    }

    public function getMenus()
    {
    	// Cache::forget('menu_module');
    	$menus = Cache::rememberForever('menu_module', function(){
    		return json_decode(json_encode(DB::table('admin.tab_acl_menu')->get([
	    		DB::raw('cod_menu AS id'), 
	    		DB::raw("'/link/'||des_menu AS linkhref"),
	    		DB::raw("des_menu AS description"),
	    		DB::raw('cod_menu_pai AS parent_id')
	    		])), true);
		});

    	$menus_tree = $this->buildTree($menus);

    	$this->buildMenu($menus_tree);

    }

    protected function buildMenu($tree)
    {
    	\Menu::make('navBar', function($menu) use ($tree) {
    		foreach ($tree as $link) {
    			$item = $menu->add($link['description'], 'localink/'.$link['linkhref']);
    			if (isset($link['children'])) {
    				$this->extractChild($link['children'], $item);
    			}
    		}
    	});
    }

    public function extractChild($childs, $menu, $aa=false)
    {
    	foreach ($childs as $link) {
    		$item = $menu->add($link['description'], 'localink/'.$link['linkhref']);
    		if (isset($link['children'])) {
    			return $this->extractChild($link['children'], $item);
    		}

    	}
    }

    protected function buildTree(&$elements, $idPai=NULL)
    {
    	$branch = array();

    	foreach ($elements as &$element) {

    		if ($element['parent_id'] == $idPai) {
    			$children = $this->buildTree($elements, $element['id']);
    			if ($children) {
    				$element['children'] = $children;
    			}
    			$branch[$element['id']] = $element;
    			unset($element);
    		}
    	}

    	return $branch;
    }

}


// Build menu
// \Menu::make('MyNavBar', function($menu){
//     $links = [];
//     $links[] = array(
//         'name' => 'System of a down',
//         'link' => '',
//         'route' => '',
//         'belongs' => ''
//         );
//     $links[] = array(
//         'name' => 'Generator',
//         'link' => '/admin/g',
//         'route' => 'adm::gen::index',
//         'belongs' => 'System of a down',
//         );
//     // $a = collect($links);
//     // ;
//     // echo '<pre>';
//     // print_r($a->where("belongs",'')->all());
//     // exit('_var');

//     // foreach ($links as $link) {
//     //     $item = $menu;
//     //     if($link['belongs']){
//     //         // $item = 
//     //         $menu->{camel_case($link['belongs'])}->add($link['name'], $link['route']?:$link['link']);
//     //     } else {

//     //     }
//     // }
//     // echo url()->current();exit;


//     $menu->add('Matrix');
//     $menu->matrix->add('Generator', array('route' => 'adm::gen::index'));
//     $menu->matrix->add('Generator2', url('admin/g/config'));
//     $menu->matrix->add('Generator2', 'www.google.com');
//     // $menu->system->add('Configurator', array('route' => 'adm::config'));
//     // dd($menu->get('system'));
// });