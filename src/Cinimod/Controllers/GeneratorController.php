<?php

namespace Mkny\Cinimod\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;


use Mkny\Cinimod\Logic;
use Config;
use Session;

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

    // Script de funcionalidades do admin (movido para o middleware)
    // \Mkny\Cinimod\Logic\UtilLogic::addViewVar('scripts', ['/js/mkny.main.js']);

    if (isset($_GET['banco'])) {
      if ($_GET['banco']) {
        Session::put('banco', $_GET['banco']);
      } else {
        Session::forget('banco');
      }
    }

    if (Session::get('banco') && Session::get('banco') != 'ws') {
      Config::set('database.default', Session::get('banco'));
    }
  }

	/**
	 * Pagina para a geração dos [Modulos]
   * 
	 * @return view
	 */
	public function getIndex()
	{

    // dd(\Request::input('banco'));
    // if(Session::get('banco') == 'ws'){
      // Session::forget('banco');
      // return redirect()->route('adm::gen::index', ['wsurl' => '1']);
    // } else {
     // Busca todas as tabelas do banco
    $tables = $this->logic->buildTables();

      // Varre as tabelas para buscar relacionamentos, controllers e dados especificos
    foreach ($tables as $key => $table) {

       // Constroi um array para organizar o retorno das relacoes
      $arrRelations = [];

       // Busca os relacionamentos
      $relations = $this->logic->buildRelationships($table->schema.'.'.$table->name);
      if($relations){
        foreach ($relations as $relation) {
          // mdd($relation);
           // Formata em string, para melhor analise no codigo
          $arrRelations[] = "{$relation->schema_foreign}.{$relation->table_foreign}.{$relation->table_foreign_field} > {$relation->schema_primary}.{$relation->table_primary}.{$relation->table_primary_field}";
        }
      }

      $tables[$key]->relation = $arrRelations;

       // Monta o provavel nome do controlador
      $controller = $this->logic->controllerName($table->name);

        // Predetermina o nome do controller
      $tables[$key]->controller = $controller;

        // Verifica se o [Modulo] ja foi gerado
      $tables[$key]->is_generated = $this->files->exists(mkny_models_path($controller).'.php');
    }

      // Cria uma collection das tables
    $tables = collect($tables);

      // Schemas selecionados
    $data['schemas_selected'] = \Request::input('schema')?:array();

      // Faz o filtro inteligente nos schemas selecionados
    if (isset($data['schemas_selected']) && count($data['schemas_selected'])) { 
      $tables = $tables->whereIn('schema', $data['schemas_selected']);
    }

      // Pega todas as tabelas selecionadas
    $data['tables'] = $tables->all();

      // Schemas para a selecao
    $data['schemas'] = explode(',',$this->logic->_getSchemas());
    // }
    // Informa o database
    // $data['database'] = Session::get('banco');
    $data['database'] = Config::get('database.default');

    // Conexoes disponiveis (config/database.php)
    $data['connections'] = array_keys(Config::get('database.connections'));

    // Tratamento do WS
    // if(\Request::get('wsurl')){
    //   $ws = new \Mkny\Cinimod\Logic\WSClientLogic();
    //   $ws->init(\Request::get('wsurl'));
    //   $data['ws_methods'] = $ws->getAvailableFunctions();
    // }

    // Get the view
    return view('cinimod::admin.generator.index')->with($data);
  }

    /**
     * Recebe os dados para a geração dos [Modulos]
     * 
     * @param  Request $request
     * @return view
     */
    public function postIndex(Request $request){

      // Aumenta um pouco o tempo de execucao do script
      ini_set('max_execution_time', 300);

      // Filtra o postData
      $post = $request->only('table', 'controller', 'banco');

      // Verifica os dados enviados
      if(!is_array($post['controller'])){
        return back()->with(array(
          'status' => 'danger',
          'message' => 'Selecione um ou mais [Modulos] para gerar'
          ));
      }

      // Conta os items de sucesso
      $countSuccess = 0;

      // Varre os controladores, em busca dos dados e monta
      foreach ($post['controller'] as $indice => $controller) {
        $table = $post['table'][$indice];
        // Chama o gerador
        try {
          // Call do artisan [modulo]
          \Artisan::call('mkny:modulo', [
            'modulo' => $controller,
            'tabela' => $table
            ]);
          $countSuccess++;
        } catch (Exception $e) {

        }
      }

      // Retorna para a acao inicial
      return back()->with(array(
        'status' => 'success',
        'message' => "{$countSuccess}/".(count($post['controller']))." [Modulos] gerados!"
        ));
    }

    /**
     * Funcao para automatizacao da exclusao das tabelas
     * 
     * @param string $controller Tabela informada
     * @return void
     */
    public function getDeleter($controller){
      try {
        \Artisan::call('mkny:deleter', [
         'controller' => $controller,
         '--force' => true
         ]);
        return back()->with(array(
          'status' => 'danger',
          'message' => 'Excluido!'
          )); 
      } catch (Exception $e) {
        return back()->with(array(
          'status' => 'danger',
          'message' => 'O item nao pode ser excluido!'
          )); 
      }
    }

    /**
     * Funcao para edicao da config do [Modulo]
     * @param string $controller Nome do controlador
     * @return void
     */
    public function getConfig($controller=false)
    {
      // $this->logic->_getConfigFiles();
      if($controller){
        // Busca o arquivo especificado
        $cfg_file = mkny_model_config_path($controller).'.php';

        // Field types
        $f_types = array_unique(array_values($this->logic->_getFieldTypes()));

        // Config file data
        $config_str = $this->files->getRequire($cfg_file);
        
        // Pula o primeiro indice
        // array_shift($config_str);
        $valOrder=1;
        // Fornece o tipo "types" para todos os campos, para selecao
        foreach ($config_str as $key => $value) {

          $config_str[$key]['name'] = $key;
          $config_str[$key]['type'] = isset($value['type']) ? $value['type']:'string';
          $config_str[$key]['form_add'] = isset($value['form_add']) ? $value['form_add']:true;
          $config_str[$key]['form_edit'] = isset($value['form_edit']) ? $value['form_edit']:true;
          $config_str[$key]['grid'] = isset($value['grid']) ? $value['grid']:true;
          $config_str[$key]['relationship'] = isset($value['relationship']) ? $value['relationship']:false;
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

      return view('cinimod::admin.generator.config')->with('configs', $this->_getConfigFiles());
    }

    /**
     * Recebe o array da config e monta corretamente
     * 
     * @param string $controller Nome do controlador
     * @return void
     */
    public function postConfig($module){
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
      $configs = $this->files->files(mkny_model_config_path());

      // Monta o array
      $arrConfig = array();
      foreach ($configs as $config) {
        $arrConfig[] = substr(class_basename($config),0,-4);
      }

      return $arrConfig;
    }


    /**
     * Varre o diretorio em busca de arquivos de traducao
     * 
     * @return array
     */
    public function _getTransFiles($langpack)
    {
      $arrExclude = array('auth', 'pagination', 'passwords', 'validation');
      // Pega todos os arquivos do diretorio
      $configs = $this->files->files(mkny_lang_path().$langpack.'/');

      // Monta o array
      $arrConfig = array();
      foreach ($configs as $config) {
        $c_data = substr(class_basename($config),0,-4);
        if(in_array($c_data, $arrExclude)){
          continue;
        }
        $arrConfig[] = $c_data;
      }

      return $arrConfig; 
    }

    private function _getLangPacks()
    {
      $langFiles = array();

      $langs = $this->files->directories(mkny_lang_path());
      foreach ($langs as $lang) {

        $langFiles[] = class_basename($lang);
      }

      return $langFiles;
    }

    public function postTrans($lang, $module=false)
    {
      // Armazena os dados enviados
      $req_fields = \Request::all();

      if(isset($req_fields['new_fields'])){
        // Percorre todos os indices, em busca de novos fields
        foreach ($req_fields['new_fields']['key'] as $key_field => $new_field) {
          $to_set = $req_fields['new_fields']['value'][$key_field];

          // Faz o nest pro item
          \Mkny\Cinimod\Logic\UtilLogic::setNestedArrayValue($req_fields,$new_field, $to_set, '.');
        };

        unset($req_fields['new_fields']);
      }

      // mdd($req_fields);
      
      // if (isset($req_fields['new_file_name'])) {
        // return redirect()->route('adm::trans', [$lang, $req_fields['new_file_name']]);
      // }
      
      // Arquivo de configuracao
      $cfg_file = mkny_lang_path($lang.'/'.$module).'.php';


      \Mkny\Cinimod\Logic\UtilLogic::updateConfigFile($cfg_file, $req_fields);
      
      // Volta para a tela de selecao
      return redirect()->route('adm::trans')->with(array(
        'status' => 'success',
        'message' => 'Arquivo atualizado!'
        ));
    }


    /**
     * Funcao para edicao da traducao do [Modulo]
     * @param string $controller Nome do controlador
     * @return void
     */
    public function getTrans($lang=false, $controller=false)
    {
      if(!$lang){
        $lang = \App::getLocale();
      }


      if($controller){
        // Busca o arquivo especificado
        $cfg_file = mkny_lang_path($lang.'/'.$controller).'.php';

        // Field types
        $f_types = array_unique(array_values($this->logic->_getFieldTypes()));

        // Se o diretorio nao existir
        if (!realpath(dirname($cfg_file))) {
          $this->files->makeDirectory(dirname($cfg_file));
          // echo dirname($cfg_file);exit;
        }

        // Config file data
        if(!$this->files->exists($cfg_file)){

          $this->files->put($cfg_file, '<?php return array();');
        }
        $config_str = $this->files->getRequire($cfg_file);

        // echo '<pre>';
        // print_r($config_str);
        // exit;

        $arrFields = array();
        foreach ($config_str as $field_name => $field_value) {
          if(!is_string($field_value)){


            // $field_name = $field_name.'[]';
            // foreach ($field_value as $k_key => $k_value) {

            // }
            // $arrFields[$field_name] = array(
            //   'name' => $field_name,
            //   // 'default_value' => $field_value,
            //   'type' => 'string',
            //   'trans' => $field_name
            //   );

            // echo '<pre>';
            // print_r($arrFields);
            // exit;
            // mdd($field_value);
            // array('name')
            // mdd($field_value);
            $arrFields[$field_name] = array(
              'name' => $field_name,
              'trans' => $field_name,
              'values' => $field_value,
              'type' => 'multi',
              );
          } else {
            $arrFields[$field_name] = array(
              'name' => $field_name,
              'trans' => $field_name,
              'default_value' => $field_value,
              'type' => 'string',
              );
          }
          
        }
        // mdd($arrFields);
        

        $form = new \Mkny\Cinimod\Logic\FormLogic();
        $d = $form->getForm(false,route('adm::trans', [$lang, $controller]),$arrFields, $controller);

        // $cl = new \Mkny\Cinimod\Logic\CRUDLogic();
        // $d = $cl->getForm(false,route('adm::trans', [$lang, $controller]),$arrFields, $controller);



        return view('cinimod::admin.generator.trans_detailed')->with(['form' => $d]);
        // echo '<pre>';
        // print_r($config_str);
        // exit;
        // Pula o primeiro indice
        // array_shift($config_str);
        // $valOrder=0;
        // // Fornece o tipo "types" para todos os campos, para selecao
        // foreach ($config_str as $key => $value) {

        //   $config_str[$key]['name'] = $key;
        //   $config_str[$key]['type'] = isset($value['type']) ? $value['type']:'string';
        //   $config_str[$key]['form'] = isset($value['form']) ? $value['form']:true;
        //   $config_str[$key]['grid'] = isset($value['grid']) ? $value['grid']:true;
        //   $config_str[$key]['relationship'] = isset($value['relationship']) ? $value['relationship']:false;
        //   $config_str[$key]['searchable'] = isset($value['searchable']) ? $value['searchable']:false;
        //   $config_str[$key]['order'] = isset($value['order']) ? $value['order']:$valOrder++;

        //   $config_str[$key]['types'] = array_combine($f_types,$f_types);
        // }

        // if (isset(array_values($config_str)[1]['order'])) {
        //     usort(($config_str), function($dt, $db){
        //         if(!isset($db['order'])){
        //             $db['order'] = 0;
        //         }
        //         if(isset($dt['order'])){
        //             return $dt['order'] - $db['order'];
        //         } else {
        //             return 0;
        //         }
        //     });

        //     $newConfig = [];
        //     foreach ($config_str as $sortfix) {
        //         $newConfig[$sortfix['name']] = $sortfix;
        //     }
        //     $config_str = $newConfig;
        // }

        // $data['controller'] = $controller;
        // $data['data'] = $config_str;

        // return view('cinimod::admin.generator.config_detailed_new')->with($data);
        // return view('cinimod::admin.generator.config_detailed')->with($data);
      }

      return view('cinimod::admin.generator.trans')->with([
        'langlist' => $this->_getLangPacks(),
        'langlist_sel' => $lang,
        'langfiles' => $this->_getTransFiles($lang)
        ]);
    }
  }
