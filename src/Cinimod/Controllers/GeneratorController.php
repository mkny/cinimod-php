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

    

}
