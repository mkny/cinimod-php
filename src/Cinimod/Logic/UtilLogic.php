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
        $config_str = \File::getRequire($file);


        $config_new = array_except($data, array('_token'));
        // $config_new = array_only($data, array_merge(array_keys($config_str),array('new_fields')));


        // mdd($config_new);
        if (isset($config_new['new_fields'])) {
            $nf = $config_new['new_fields'];

            foreach ($nf as $n_field) {
                $config_new[$n_field['name']] = $n_field;
            }

            unset($config_new['new_fields']);
        }

        // Resultado gerado
        $new_config_str = array_replace_recursive(isset($config_str['fields']) ? $config_str['fields']:$config_str, $config_new);
        // $new_config_str = array_replace_recursive($config_str['fields'], $config_new);

        // Tratamento do true / false
        foreach ($new_config_str as $key => $value) {

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
                    $new_config_str[$key][$vKey] = $vValue;
                }
            }
        }



        
        $config_str = isset($config_str['fields']) ? ['fields' => $new_config_str]:$new_config_str;;


        
        // Monta a string corretamente para gravar
        $string = '<?php return '.var_export($config_str,true).';';

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

}
