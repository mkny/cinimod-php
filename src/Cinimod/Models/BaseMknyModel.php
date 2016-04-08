<?php

namespace Mkny\Cinimod\Models;

use Illuminate\Database\Eloquent\Model;

use Mkny\Cinimod\Logic;


class BaseMknyModel extends Model
{

	/**
	 * Funcao para buscar os dados, para auxiliar na listagem
	 * 
	 * @param  array Parametros de configuracao [field_key (campo com o id),field_show (campo com o nome),where (filtro a ser aplicado)]
	 * @param  int Id para filtrar direto
	 * @return array
	 */
    static public function relation($conf,$filterID=false)
    {

        // echo '<pre>';
        // print_r($conf['field_key'] = $filterID);
        // exit;
    	$fields = [];
        // $fields[] = $conf['field_key']. ' AS id';
    	$fields[] = $conf['field_fkey']. ' AS id';
    	$fields[] = $conf['field_show'].' AS name';

        $model = $conf['model']::orderBy($conf['field_show'], 'ASC');
        // if(isset($conf['where']) && !empty(trim($conf['where']))) {
        //     $model->whereRaw(implode(' and ', $conf['where']));
        // }

        if ($filterID) {
            $model->whereRaw("{$conf['dependsOn']} = {$filterID}");
        }

        return $model->get($fields);

    }

    /**
     * Funcao para buscar a configuracao do [Model], baseado na area que ele esteja sendo chamado
     * 
     * @param string $type
     * @return array
     */
    public function _getConfig($type='all'){
        $config = [];


        $cfg = Logic\UtilLogic::load(mkny_model_config_path(class_basename($this)).'.php');

        
        switch ($type) {
            case 'datagrid':
            $config = array_filter($cfg, function($var){
                return isset($var['grid']) && $var['grid'] == 1;
            });
            break;
            case 'form':
            $config = array_filter($cfg, function($var){
                return isset($var['form']) && $var['form'] == 1;
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

            $newConfig = [];
            foreach ($config as $sortfix) {
                $newConfig[$sortfix['name']] = $sortfix;
            }
            $config = $newConfig;
        }

        return $config;
    }

}
