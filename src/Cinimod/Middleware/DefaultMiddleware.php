<?php

namespace Mkny\Cinimod\Middleware;

use Closure;
use DB;
use Cache;
use Illuminate\Http\Request;
use Route;

use Mkny\Cinimod\Logic;
// use Lavary\Menu;


abstract class DefaultMiddleware
{
    private $request;

    protected $module;
    protected $action;
    protected $controller;

    abstract protected function getAmbient();

    private function setAmbient(Request $request)
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

        $this->setAmbient($request);

        $this->getMenus();

        return $next($request);
    }

    public function getMenus()
    {
    	// Cache::forget('menu_module');
    	// $menus = Cache::rememberForever('menu_module', function(){
    	// 	return json_decode(json_encode(DB::table('admin.tab_acl_menu')->get([
	    // 		DB::raw('cod_menu AS id'), 
	    // 		DB::raw("'/link/'||des_menu AS linkhref"),
	    // 		DB::raw("des_menu AS description"),
	    // 		DB::raw('cod_menu_pai AS parent_id')
	    // 		])), true);
     //    });
        $menus = [
        [
        'id' => '1',
        'linkhref' => 'admin/',
        'description' => 'Gerencial',
        'parent_id' => '',
        ],
        [
        'id' => '2',
        'linkhref' => 'admin/g',
        'description' => 'Gerador',
        'parent_id' => '1',
        ],
        [
        'id' => '3',
        'linkhref' => 'admin/g/config',
        'description' => 'Configurador',
        'parent_id' => '1',
        ],
        [
        'id' => '4',
        'linkhref' => 'admin/g/trans',
        'description' => 'Tradutor',
        'parent_id' => '1',
        ],
        ];

        $menus_tree = $this->buildTree($menus);

        $this->buildMenu($menus_tree);

    }

    protected function buildMenu($tree)
    {
    	\Menu::make('navBar', function($menu) use ($tree) {
    		foreach ($tree as $link) {

    			// $item = $menu->add($link['description'], array(
    			// 	'action' => 'CidadeController@getEdit'
    			// 	));
    			$item = $menu->add($link['description'], $link['linkhref']);

    			if (isset($link['children'])) {
    				$this->extractChild($link['children'], $item);
    			}
    		}
    	});
    }

    public function extractChild($childs, $menu, $aa=false)
    {
    	foreach ($childs as $link) {
    		$item = $menu->add($link['description'], $link['linkhref']);
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

/**
 * 
 * 
 * 

 * 
 */