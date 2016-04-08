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

}
