<?php

namespace Mkny\Cinimod\Logic;

use DB;

use Illuminate\Filesystem\Filesystem;

class UtilLogic {

    /**
     * Funcao para traduzir a stub
     * 
     * @param  string &$stub Stub Model
     * @return void
     */
    static public function translateStub($translation, &$stub)
    {
    	foreach ($translation as $varName => $varValue) {
    		$stub = str_replace("{{{$varName}}}", $varValue, $stub);
    	}
    }

    static public function load($file)
    {
    	$fs = app()->make('Illuminate\Filesystem\Filesystem');
    	if($fs->exists($file)){
    		return $fs->getRequire($file);
    	}
    	return null;
    }

    /**
     * Um Helper pra ajudar a settar variaveis no ambiente, utilizando o Blade
     * @param string $key Nome da variavel comum
     * @param string|mixed $value Valor da variavel
     * @return void
     */
    static public function addViewVar($key, $value=false)
    {
    	$old = view()->shared($key);

    	if(is_array($old) && is_array($value)){
    		$value = array_merge($old, $value);
    	}

    	view()->share($key, $value);
    }

    
    static public function array_finder($array, $key)
    {
    	foreach ($array as $k => $v) {
    		if($v['name'] == $key){
    			return $k;
    		}
    	}
    	return false;
    }

    static public function updateConfigFile($file,$data)
    {

        // String do arquivo
    	$arrConfigFileData = \File::getRequire($file);


    	$config_new = array_except($data, array('_token'));

        // $config_new = array_only($data, array_merge(array_keys($arrConfigFileData),array('new_fields')));



    	if (isset($config_new['new_fields'])) {
    		$nf = $config_new['new_fields'];

    		foreach ($nf as $n_field) {
    			$config_new[$n_field['name']] = $n_field;
    		}

    		unset($config_new['new_fields']);
    	}

        // Resultado gerado
    	$arrCamposData = array_replace_recursive(isset($arrConfigFileData['fields']) ? $arrConfigFileData['fields']:$arrConfigFileData, $config_new);
        // $arrCamposData = array_replace_recursive($arrConfigFileData['fields'], $config_new);

        // Tratamento do true / false
    	foreach ($arrCamposData as $key => $value) {
    		if(is_array($value)){
    			foreach ($value as $vKey => $vValue) {

    				if(in_array($vKey, array('order'))){
    					continue;
    				}
    				if($vValue == '0' || $vValue === false){
                    // Forca false
    					$vValue = false;
    				} elseif($vValue == '1' || $vValue === true){
                    // Forca true
    					$vValue = true;
    				}

                // Adiciona o valor no novo array
    				$arrCamposData[$key][$vKey] = $vValue;
    			}
    		}
    	}



    	if(isset($arrConfigFileData['fields'])){
    		$arrConfigFileData['fields'] = $arrCamposData;
    	} else {
    		$arrConfigFileData = $arrCamposData;
    	}

        // $arrConfigFileData = isset($arrConfigFileData['fields']) ? ['fields' => $arrCamposData]:$arrCamposData;
        // mdd($arrConfigFileData);
        // mdd($arrCamposData);
        // $arrConfigFileData = isset($arrConfigFileData['fields']) ? ['fields' => $arrCamposData]:$arrCamposData;;



        // Monta a string corretamente para gravar
    	$string = '<?php return '.var_export($arrConfigFileData,true).';';

        // Grava no arquivo
    	return \File::put($file, $string);
    }



    /**
     * Sets a value in a nested array based on path
     * See http://stackoverflow.com/a/9628276/419887
     *
     * @param array $array The array to modify
     * @param string $path The path in the array
     * @param mixed $value The value to set
     * @param string $delimiter The separator for the path
     * @return The previous value
     */
    static function setNestedArrayValue(&$array, $path, &$value, $delimiter = '/') {
    	$pathParts = explode($delimiter, $path);

    	$current = &$array;
    	foreach($pathParts as $key) {
    		$current = &$current[$key];
    	}

    	$backup = $current;
    	$current = $value;

    	return $backup;
    }



    /**
     * Funcao para buscar a configuracao do [Model], baseado na area que ele esteja sendo chamado
     * 
     * @param string $type
     * @return array
     */
    public function _getConfig($model, $type='all'){
    	$config = [];


    	$cfg = UtilLogic::load(mkny_model_config_path($model).'.php')['fields'];
        // mdd($cfg);
        // $cfg = Logic\UtilLogic::load(mkny_model_config_path(class_basename($model)).'.php')['fields'];

    	switch ($type) {
    		case 'datagrid':
    		$config = array_filter($cfg, function($var){
    			return isset($var['grid']) && $var['grid'] == 1;
    		});
    		break;
    		case 'form_add':
    		$config = array_filter($cfg, function($var){
    			return isset($var['form_add']) && $var['form_add'] == 1;
    		});
    		break;
    		case 'form_edit':
    		$config = array_filter($cfg, function($var){
    			return isset($var['form_edit']) && $var['form_edit'] == 1;
    		});
    		break;
    		case 'search':
    		$config = array_filter($cfg, function($var){
    			return isset($var['searchable']) && $var['searchable'] == 1;
    		});
    		break;
    		case 'all':
    		default:
    		$config = $cfg;
    		break;
    	}

    	if (isset(array_values($config)[1]['order'])) {
    		usort(($config), function($dt, $db){
    			if(!isset($db['order'])){
    				$db['order'] = 0;
    			}
    			if(isset($dt['order'])){
    				return $dt['order'] - $db['order'];
    			} else {
    				return 0;
    			}
    		});
            // mdd($config);
    		$newConfig = [];
    		foreach ($config as $sortfix) {
    			$newConfig[$sortfix['name']] = $sortfix;
    		}
    		$config = $newConfig;
    	}

    	return $config;
    }

    public function makeTag($tag, Array $config=array())
    {
    	foreach ($config['attributes'] as $key => $attr) {
    		if(is_array($attr)){
    			$config['attributes'] = array_merge($config['attributes'], array_combine(array_map(function($attr_key) use ($key) {
    				return $key.'-'.$attr_key;
    			}, array_keys($attr)), $attr));
    			unset($config['attributes'][$key]);
    		}
    	}

    	if (isset($config['link'])) {
    		$config['attributes']['href'] = $config['link'];
    	};


        // mdd($config);

    	return \Html::tag($tag, isset($config['text']) ? $config['text']:'', $config['attributes']);
    }

    public function getMenus()
    {
        // Cache::forget('menu_module');
        // $menus = Cache::rememberForever('menu_module', function(){
        //  return json_decode(json_encode(DB::table('admin.tab_acl_menu')->get([
        //      DB::raw('cod_menu AS id'), 
        //      DB::raw("'/link/'||des_menu AS linkhref"),
        //      DB::raw("des_menu AS description"),
        //      DB::raw('cod_menu_pai AS parent_id')
        //      ])), true);
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
                //  'action' => 'CidadeController@getEdit'
                //  ));
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

    public function parseFilter(&$mconfig)
    {
    	$filters = \Request::input('filter');
        // mdd($filters);



    	$mconfig = array_map(function($field_config) use ($filters){
    		$strf = '%'.str_replace(' ', '%', $filters['global']).'%';

    		// Verifica o envio da informacao global
    		if (isset($filters['global']) && $filters['global']) {
    			$field_config['filter'] = ['global' => "sem_acento({$field_config['name']}::varchar) ilike sem_acento('{$strf}')"];
    		}

    		// Verifica se o campo eh um relacionamento, para fazer a "gambis"
    		if (isset($field_config['relationship']) && $field_config['relationship']) {
    			// $field_config['name'];
    			
    			// mdd($field_config['relationship']);
    			$subselect_where = "
    			select {$field_config['relationship']['field_key']}
    			from ".app()->make($field_config['relationship']['model'])->getTable()."
    			where {$field_config['relationship']['field_show']} ilike '{$strf}'";
    			// mdd($mfg);
    			// "{$field_config['relationship']['field_key']} in ($mfg)";
    			$field_config['filter'] = ["global" => "{$field_config['relationship']['field_key']} in ({$subselect_where})"];
    			// $field_config['relationship']['where'][] = "{$field_config['relationship']['field_show']} ilike '%{$strf}%'";
    		}
            // mdd($field_config);
    		return $field_config;
    	}, $mconfig);


        // mdd($mconfig);


    }

}
