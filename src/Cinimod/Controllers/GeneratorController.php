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

    if (Session::get('banco')) {
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
          // Formata em string, para melhor analise no codigo
          $arrRelations[] = "{$relation->table_foreign}.{$relation->table_foreign_field} > {$relation->table_primary}.{$relation->table_primary_field}";
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
    
    // Informa o database
    $data['database'] = Config::get('database.default');

    // Schemas para a selecao
    $data['schemas'] = explode(',',$this->logic->_getSchemas());

    // Schemas selecionados
    $data['schemas_selected'] = \Request::input('schema')?:array();

    // Conexoes disponiveis (config/database.php)
    $data['connections'] = array_keys(Config::get('database.connections'));

    // Faz o filtro inteligente nos schemas selecionados
    if (count($data['schemas_selected'])) { 
      $tables = $tables->whereIn('schema', $data['schemas_selected']);
    }

    // Pega todas as tabelas selecionadas
    $data['tables'] = $tables->all();

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
        array_shift($config_str);

        // Fornece o tipo "types" para todos os campos, para selecao
        foreach ($config_str as $key => $value) {

          $config_str[$key]['name'] = $key;
          $config_str[$key]['type'] = isset($value['type']) ? $value['type']:'string';
          $config_str[$key]['form'] = isset($value['form']) ? $value['form']:true;
          $config_str[$key]['grid'] = isset($value['grid']) ? $value['grid']:true;
          $config_str[$key]['search'] = isset($value['search']) ? $value['search']:false;

          $config_str[$key]['types'] = $f_types;
        }
        // echo '<pre>';
        // print_r($config_str);
        // exit;

        $data['controller'] = $controller;
        $data['data'] = $config_str;

        return view('cinimod::admin.generator.config_detailed')->with($data);
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

      $cfg_file = mkny_model_config_path($module).'.php';
      $config_str = $this->files->getRequire($cfg_file);
      $new_config_str = array_replace_recursive($config_str, array_filter(\Request::only(array_keys($config_str))));

      // Tratamento do true / false
      foreach ($new_config_str as $key => $value) {
        foreach ($value as $vKey => $vValue) {
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

      // Monta a string corretamente para gravar
      $string = '<?php return '.var_export($new_config_str,true).';';

      // Grava no arquivo
      $this->files->put($cfg_file, $string);

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
  }
