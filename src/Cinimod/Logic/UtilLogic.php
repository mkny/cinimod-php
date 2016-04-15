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


        $config_new = array_only($data, array_merge(array_keys($config_str),array('new_fields')));

        if (isset($config_new['new_fields'])) {
            $nf = $config_new['new_fields'];

            foreach ($nf as $n_field) {
                $config_new[$n_field['name']] = $n_field;
            }

            unset($config_new['new_fields']);
        }

        // Resultado gerado
        $new_config_str = array_replace_recursive($config_str, $config_new);
        // echo '<pre>';
        // print_r($new_config_str);
        // exit;
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

        // Monta a string corretamente para gravar
        $string = '<?php return '.var_export($new_config_str,true).';';
        // echo '<pre>';
        // print_r($file);
        // exit;
        // Grava no arquivo
        return \File::put($file, $string);
    }

}
