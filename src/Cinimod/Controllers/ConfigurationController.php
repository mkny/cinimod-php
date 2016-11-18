<?php

namespace Mkny\Cinimod\Controllers;

use App\Http\Controllers\Controller;

// use Mkny\Cinimod\Logic\AppLogic;

/**
* 
*/
class ConfigurationController extends Controller
{

	public function getIndex()
	{

		return view('cinimod::admin.generator.config')->with('configs', $this->_getConfigFiles());
	}

    /**
     * Funcao para edicao da config do [Modulo]
     * @param string $controller Nome do controlador
     * @return void
     */
    public function getFile($controller)
    {
      	// $this->logic->_getConfigFiles();
    	
        	// Busca o arquivo especificado
    	$cfg_file = mkny_model_config_path($controller).'.php';

        	// Field types
    	$f_types = array_unique(array_values(app()->make('Mkny\Cinimod\Logic\AppLogic')->_getFieldTypes()));

        	// Se o diretorio nao existir
    	if (!realpath(dirname($cfg_file))) {
    		\File::makeDirectory(dirname($cfg_file));
    	}

        	// Config file data
    	if(!\File::exists($cfg_file)){
    		$stub = \File::get(__DIR__.'/../Commands/stubs/model_config.stub');

    		\Mkny\Cinimod\Logic\UtilLogic::translateStub(array(
    			'var_fields_data' => ''
    			), $stub);

    		\File::put($cfg_file, $stub);
    	}

        	// Config file data
    	$config_str = \File::getRequire($cfg_file)['fields'];

	        // Pula o primeiro indice
	        // array_shift($config_str);
    	$valOrder=1;
        	// Fornece o tipo "types" para todos os campos, para selecao
    	foreach ($config_str as $key => $value) {
    		if(!is_array($config_str[$key])){
    			$config_str[$key] = array();
    		}

    		$config_str[$key]['name'] = $key;

    		$config_str[$key]['type'] = isset($value['type']) ? $value['type']:'string';
    		$config_str[$key]['form_add'] = isset($value['form_add']) ? $value['form_add']:true;
    		$config_str[$key]['form_edit'] = isset($value['form_edit']) ? $value['form_edit']:true;
    		$config_str[$key]['grid'] = isset($value['grid']) ? $value['grid']:true;
    		$config_str[$key]['relationship'] = isset($value['relationship']) ? $value['relationship']:false;
    		$config_str[$key]['required'] = isset($value['required']) ? $value['required']:false;
    		$config_str[$key]['searchable'] = isset($value['searchable']) ? $value['searchable']:false;
    		$config_str[$key]['order'] = isset($value['order']) ? $value['order']:$valOrder++;

    		$config_str[$key]['types'] = array_combine($f_types,$f_types);
    	}

    	if (isset(array_values($config_str)[1]['order'])) {
    		usort(($config_str), function($dt, $db){
    			if(!isset($db['order'])){
    				$db['order'] = 0;
    			}
    			if(isset($dt['order'])){
    				return $dt['order'] - $db['order'];
    			} else {
    				return 0;
    			}
    		});

    		$newConfig = [];
    		foreach ($config_str as $sortfix) {
    			$newConfig[$sortfix['name']] = $sortfix;
    		}
    		$config_str = $newConfig;
    	}

    	$data['controller'] = $controller;
    	$data['data'] = $config_str;

    	return view('cinimod::admin.generator.config_detailed_new')->with($data);
        	// return view('cinimod::admin.generator.config_detailed')->with($data);
    	
    }

    /**
     * Recebe o array da config e monta corretamente
     * 
     * @param string $controller Nome do controlador
     * @return void
     */
    public function postFile($module){
      	// Arquivo de configuracao
    	$cfg_file = mkny_model_config_path($module).'.php';

    	\Mkny\Cinimod\Logic\UtilLogic::updateConfigFile($cfg_file, \Request::all());

      	// Volta para a tela de selecao
    	return redirect()->route('adm::config')->with(array(
    		'status' => 'success',
    		'message' => 'Arquivo atualizado!'
    		));
    }

    /**
     * Varre o diretorio em busca de arquivos de configuracao
     * 
     * @return array
     */
    private function _getConfigFiles()
    {
      	// Pega todos os arquivos do diretorio
    	$configs = \File::files(mkny_model_config_path());

      	// Monta o array
    	$arrConfig = array();
    	foreach ($configs as $config) {
    		$arrConfig[] = substr(class_basename($config),0,-4);
    	}

    	return $arrConfig;
    }
}
