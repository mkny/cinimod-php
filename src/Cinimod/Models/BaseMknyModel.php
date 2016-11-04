<?php

namespace Mkny\Cinimod\Models;

use Illuminate\Database\Eloquent\Model;

use Mkny\Cinimod\Logic;
use DB;

class BaseMknyModel extends Model
{
    
    /**
     * Funcao para implementar os filtros dinamicos, 
     * definidos no arquivo de configuracao (modelconfig)
     * 
     * @param  boolean $excludeDeleted
     * @return Object
     */
    public function newQuery($excludeDeleted = true) {

        $filters = $this->_getDefaultFilter();

        $query = parent::newQuery($excludeDeleted = true);
        // Illuminate\Database\Eloquent\Builder
        
        
        // $query->whereHas('cod_paiss', function($querys){
        //     mdd(get_class($querys));
        //     $querys->where('ind_status', '=', 'A');
        // });

        foreach ($filters as $filter) {
            $query->whereRaw($filter);
        }
        
        return $query;
    }

	/**
	 * Funcao para buscar os dados, para auxiliar na listagem
	 * 
	 * @param  array Parametros de configuracao [field_key (campo com o id),field_show (campo com o nome),where (filtro a ser aplicado)]
	 * @param  int Id para filtrar direto
	 * @return array
	 */
    static public function relation($conf,$filterID=false)
    {

    	$fields = [];
        // $fields[] = $conf['field_key']. ' AS id';
    	$fields[] = $conf['field_fkey']. ' AS id';
    	$fields[] = DB::raw($conf['field_show'].' AS name');
        // mdd($fields);

        $model = $conf['model']::orderBy($conf['field_show'], 'ASC');
        // if(isset($conf['where']) && !empty(trim($conf['where']))) {
        //     $model->whereRaw(implode(' and ', $conf['where']));
        // }

        if ($filterID) {
            $model->whereRaw("{$conf['dependsOn']} = {$filterID}");
        }

        return $model->get($fields);

    }

    // /**
    //  * Funcao para buscar a configuracao do [Model], baseado na area que ele esteja sendo chamado
    //  * 
    //  * @param string $type
    //  * @return array
    //  */
    public function _getConfig($type='all'){
        throw new \Exception('Rebuild this using UtilLogic!');
    }
    // public function _getConfig($type='all'){
    //     $config = [];


    //     $cfg = Logic\UtilLogic::load(mkny_model_config_path(class_basename($this)).'.php')['fields'];
        
    //     switch ($type) {
    //         case 'datagrid':
    //         $config = array_filter($cfg, function($var){
    //             return isset($var['grid']) && $var['grid'] == 1;
    //         });
    //         break;
    //         case 'form_add':
    //         $config = array_filter($cfg, function($var){
    //             return isset($var['form_add']) && $var['form_add'] == 1;
    //         });
    //         break;
    //         case 'form_edit':
    //         $config = array_filter($cfg, function($var){
    //             return isset($var['form_edit']) && $var['form_edit'] == 1;
    //         });
    //         break;
    //         case 'search':
    //         $config = array_filter($cfg, function($var){
    //             return isset($var['searchable']) && $var['searchable'] == 1;
    //         });
    //         break;
    //         case 'all':
    //         default:
    //         $config = $cfg;
    //         break;
    //     }

    //     if (isset(array_values($config)[1]['order'])) {
    //         usort(($config), function($dt, $db){
    //             if(!isset($db['order'])){
    //                 $db['order'] = 0;
    //             }
    //             if(isset($dt['order'])){
    //                 return $dt['order'] - $db['order'];
    //             } else {
    //                 return 0;
    //             }
    //         });

    //         $newConfig = [];
    //         foreach ($config as $sortfix) {
    //             $newConfig[$sortfix['name']] = $sortfix;
    //         }
    //         $config = $newConfig;
    //     }

    //     return $config;
    // }

    /**
     * Retorna as configuracoes do formulario
     * 
     * @return array
     */
    public function _getFormConfig()
    {
        return Logic\UtilLogic::load(mkny_model_config_path(class_basename($this)).'.php')['form'];
    }

    /**
     * Retorna as configuracoes do datagrid
     * //Retorna o filtro padrao (definido no arquivo de configuracao)
     * 
     * @return array Filtros where em 'raw' ja tratado
     */
    protected function _getDefaultFilter()
    {
        // exit('abacate');
        $filters = Logic\UtilLogic::load(mkny_model_config_path(class_basename($this)).'.php')['grid']['pre-filter'];
        
        foreach ($filters as $kfilter => $filter) {
            // Tratamento para parametros de where
            $matches = array();
            
            // Verifica as ocorrencias de variaveis enclausuradas
            preg_match('/\{(.*?)\}/', $filter, $matches);
            if(count($matches)){
                $replace = null;
                eval('$replace = ' . $matches[1] . ';');

                $filters[$kfilter] = str_replace($matches[0], $replace, $filter);
            }
        }
        
        return $filters;
    }

}
