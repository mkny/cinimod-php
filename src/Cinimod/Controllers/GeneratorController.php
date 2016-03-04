<?php

namespace Mkny\Cinimod\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;


use Mkny\Cinimod\Logic;
use Config;
use Session;
// use Input;

// Test only
// use Schema;
// Test only

/**
 * Notes
 *
 * Implementar seleção da conexão
 */
class GeneratorController extends Controller
{
	/**
	 * Logic application
	 * @var object
	 */
	private $logic;

	/**
	 * Filesystem
	 */
	private $files;
	/**
	 * Construtor da classe
	 */
	public function __construct(Filesystem $files, Logic\AppLogic $logic){
		$this->logic = $logic;
		$this->files = $files;

    \Mkny\Cinimod\Logic\UtilLogic::addViewVar('scripts', ['/javascripts/admin/admin-main.js']);



    if (isset($_GET['banco'])) {
      if ($_GET['banco']) {
        Session::put('banco', $_GET['banco']);
      } else {
        Session::forget('banco');
      }
    }

    if (Session::get('banco')) {
      Config::set('database.default', Session::get('banco'));
    }
  }

	/**
	 * Monta o html para a geração dos [Modulos]
	 * @return view
	 */
	public function getIndex()
	{
    // Busca todas as tabelas do banco
    $tables = $this->logic->buildTables();
    // dd($tables);
    // Varre as tabelas para buscar relacionamentos, controllers e dados especificos
    foreach ($tables as $key => $t) {
      // Busca os relacionamentos
      $relations = $this->logic->buildRelationships($t->schema.'.'.$t->name);
      
      $arrRelations = [];
      // Formata em string, para melhor analise no codigo
      if($relations){
        foreach ($relations as $rel) {
          $arrRelations[] = "{$rel->table_foreign}.{$rel->table_foreign_field} > {$rel->table_primary}.{$rel->table_primary_field}";
        }

      }
      $tables[$key]->relation = $arrRelations;

      // Predetermina o nome do controller
      $controller = $this->logic->controllerName($t->name);
      $tables[$key]->controller = $controller;

      // Verifica se o [Modulo] ja foi gerado
      $tables[$key]->is_generated = $this->files->exists(mkny_app_path().'/Models/'.$controller.'.php');

    }
    // Cria uma collection das tables
    $tables = collect($tables);
    
        // Informa o database
    $data['database'] = Config::get('database.default');
    $data['schemas'] = explode(',',$this->logic->_getSchemas());
    $data['schemas_selected'] = \Request::input('schema')?:array();
    $data['connections'] = array_keys(Config::get('database.connections'));

    if (count($data['schemas_selected'])) { 
      $tables = $tables->whereIn('schema', $data['schemas_selected']);
    }

    $data['tables'] = $tables->all();

    return view('admin.generator.index')->with($data);
  }

    /**
     * Recebe os dados para a geração dos [modulos]
     * @param  Request $r
     * @return view
     */
    public function postIndex(Request $r){
      ini_set('max_execution_time', 300);

        // Observa o postData
      $post = $r->only('table', 'controller', 'banco');
        // echo '<pre>';print_r($post);exit;

    	// Varre os controladores, em busca dos dados e monta
      foreach ($post['controller'] as $indice => $controller) {
        $table = $post['table'][$indice];

            // Call do artisan [modulo]
        \Artisan::call('mkny:modulo', [
         'controlador' => $controller, 'tabela' => $table
         ]);
      }


      return back()->with(array(
        'status' => 'success',
        'message' => 'Gerado com sucesso!'
        ));
      // return back()->with(array(
      //   'status' => 'success',
      //   'message' => 'Gerado com sucesso!'
      //   ));
    }

    public function getDeleter($table){

      \Artisan::call('mkny:deleter', [
       'controller' => $table,
       '--force' => true
       ]);
      return back()->with(array(
        'status' => 'danger',
        'message' => 'Excluido!'
        )); 
    }

    public function postConfig($module){
      $cfg = Logic\UtilLogic::load(mkny_app_path().'/Modelconfig/'.$module.'.php');
      $ncfg = array_replace_recursive($cfg, array_filter(\Request::only(array_keys($cfg))));

      foreach ($ncfg as $key => $value) {
        foreach ($value as $vKey => $vValue) {

          if($vValue == '0' || $vValue === false){
            $vValue = false;
          } elseif($vValue == '1' || $vValue === true){
            $vValue = true;
          }
          $ncfg[$key][$vKey] = $vValue;
        }
      }

      $string = '<?php return '.var_export($ncfg,true).';';

      
      $this->files->put(mkny_app_path().'/Modelconfig/'.$module.'.php', $string);

      return back()->with(array(
        'status' => 'success',
        'message' => 'Arquivo atualizado!'
        ));
    }

    public function getConfig(Logic\AppLogic $apl, $module=false)
    {
      if($module){
        $f_types = array_unique(array_values($apl->_getFieldTypes()));
        
        $cfg = Logic\UtilLogic::load(mkny_app_path().'/Modelconfig/'.$module.'.php');
        
        array_shift($cfg);
        foreach ($cfg as $key => $value) {
          // echo '<pre>';
          // print_r($cfg[$key]);
          // exit('_var');
          $cfg[$key][(string) 'types'] = $f_types;
        }

        // $data['action'] = '';
        $data['controller'] = $module;
        $data['data'] = $cfg;

        // return 'detailed';
        return view('admin.generator.config_detailed')->with($data);
      }

      return view('admin.generator.config')->with('configs', $this->allConfigs());
    }

    private function allConfigs()
    {
      $configs = $this->files->files(mkny_app_path().'/Modelconfig');

      $arrConfig = array();
      foreach ($configs as $config) {
        $arrConfig[] = substr(class_basename($config),0,-4);
      }
      // sort($arrConfig);
      
      return $arrConfig;
      
    }


    public function getTeste($data=false)
    {
      // $this->_getConfig();


        // $a = new $cf['model'];
        // $a->filterArray($cf['where']);
        // $aa = $a->where('ind_status', 'A')
        // ->get();

        // dd($a);

    	// echo '<pre>';print_r(Config::get('\App\games'));exit;;
    	// wecho '<pre>';print_r(include app_path().'/games.php');exit;;
    	// Config::set('database.connections.pgsql.database', 'flavia_mongo_db');
    	// echo '<pre>';print_r(Config::get('database.connections.pgsql.database'));exit;

      return 'oi';
    }
  }
