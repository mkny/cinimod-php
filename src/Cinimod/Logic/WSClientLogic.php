<?php

namespace Mkny\Cinimod\Logic;

// WS Type (soap/rest)

/**
 * Classe para tratamento de WS - Requests
 * 
 * @usage
 * 
$ws->init('http://lara.local/soap?wsdl');
$a = $ws->getTreinos();
dd($a);

or

$ws->init('http://www.predic8.com:8080/material/ArticleService?wsdl');
dd($ws->getAll());

or

$ws->init('http://www.webservicex.com/globalweather.asmx?wsdl');
dd($ws->GetCitiesByCountry('Brazil'));
 *
 * 
 */
class WSClientLogic {

	/**
	 * Armazena o objeto soap
	 * 
	 * @var \SoapClient
	 */
	private $ws_client;

	private $primaryTypes = [
	'int' => 'integer',
	'integer' => 'integer'
	];

	/**
	 * Armazena os tipos de dados da funcao
	 * 
	 * @var array
	 */
	private $ws_types;

	/**
	 * Armazena os metodos da funcao
	 * @var array
	 */
	private $ws_methods;

	/**
	 * Armazena as funcoes disponiveis no WS, com os tipos casados
	 * 
	 * @var array
	 */
	private $available_functions;

	// private $debug = false;
	
	/**
	 * Inicializador da classe
	 * 
	 * @param  string $url Url do WS
	 * @return void
	 */
	public function init($url, $config=array()){
		
		// Instancia do SoapClient
		$this->ws_client = new \SoapClient($url, $config);
		
		// Chama o construtor de metodos da classe
		return $this->build();
	}

	/**
	 * Faz a chamada do metodo do webservice
	 * 
	 * @param  string $method Nome do metodo
	 * @param  array $args Argumentos passados na funcao
	 * @return array Retorno do metodo WS
	 */
	public function __call($method, $args){
		
		// Verifica se o metodo existe no WS
		$methods = $this->getAvailableFunctions();
		if (!isset($methods[$method])) {
			abort(400, 'Metodo invalido!');
		} elseif(count($methods[$method]) > 0){

			// Se existem parametros
			$method_parameters = $methods[$method];
			if(count($method_parameters) <> count($args)){
				abort(400, 'Quantidade de parametros nao bate');
			}

			if(is_array($method_parameters)){
				// Combina os parametros solicitados, com os argumentos informados, montando o request
				$params = array_combine($method_parameters,$args);
			} else {
				$params = $args[0];
			}
		} else {
			$params = [];
		}
		
		// Passa pro tratador, apos efetuar o request no ws, fornecendo os parametros
		if(count($params) == 0){
			return $this->treatRequest($this->getWSClient()->{$method}());
		} else {
			return $this->treatRequest($this->getWSClient()->{$method}($params));
		}
	}

	/**
	 * Faz o tratamento do retorno
	 * 
	 * @todo Implementar tratamento do retorno, com os objetos
	 * 
	 * @param  \stdClass|array $request Retorno da requisicao
	 * @return array             Dados retornados
	 */
	private function treatRequest($request)
	{
		// Define um formato vazio
		$fRequest = '';

		// Preformatacao do request
		$pre = $request;

		// Tratamento para WS em .NET
		if(strstr(array_keys((array) $request)[0], 'Result') !== false){
			// Pre-formata o request (tirando o primeiro indice inutil que vem sempre)
			$pre = array_values((array) $request)[0];
		}

		// Faz o tratamento, pelo tipo de variavel
		switch(gettype($pre)){
			case 'object':
			case 'array':
			$fRequest = json_decode(json_encode($pre), true);
			break;
			case 'string':
			default:
			$fRequest = simplexml_load_string(array_values((array) $request)[0]);
			break;
		}

		// Retorna os dados
		return json_decode(json_encode($fRequest));
	}

	/**
	 * Retorna o objeto ws_client
	 * 
	 * @return \SoapClient Objeto cliente
	 */
	public function getWSClient(){
		return $this->ws_client;
	}

	/**
	 * Funcao que busca as funcoes disponiveis para serem chamadas no WS, com os parametros
	 * 
	 * @return array Funcoes => [parametros]
	 */
	public function getAvailableFunctions(){
		return $this->available_functions;
	}

	/**
	 * Constroi as funcoes para a classe
	 * 
	 * @param  array $functions Funcoes fornecidas
	 * @return array            Funcoes tratadas
	 */
	private function build_functions($functions)
	{
		// Array vazio
		$arrFunctions = array();

		// Varre as funcoes, formatando
		foreach ($functions as $func) {
			$f_parts = explode('(', explode(' ', trim($func))[1]);

			// No WS em php, vem esse indice
			if ($f_parts[1] == ')') {
				$f_parts[1] = null;
			}

			// Atribui tipo de parametro, a funcao
			$arrFunctions[$f_parts[0]] = $f_parts[1];
		}

		// Setta na classe
		return $this->ws_methods = $arrFunctions;
	}

	/**
	 * Constroi as func-types (parametros de funcao do metodo)
	 * @param  array $types Array de tipos informados
	 * @return array        Array de tipos tratados
	 */
	private function build_types($types, $types_aux=false)
	{
		// Array vazio
		$arrTypes = array();

		// Varre os tipos, formatando
		foreach ($types as $type) {
			$type_parts = explode("\n", $type);
			$typeName = explode(' ', array_shift($type_parts))[1];
			$type_parts = array_filter(array_map(function($arr){
				// Fim da linha para o tipo
				if($arr == '}'){
					return false;
				}
				return substr(explode(' ', trim($arr))[1],0,-1);
			}, $type_parts));
			$arrTypes[$typeName] = $type_parts;
			$this->ws_types[$typeName] = $type_parts;
		}

		return $this->ws_types;
	}

	/**
	 * Constroi as funcoes > parametros do WS
	 * 
	 * @return void
	 */
	private function build()
	{
		// Busca o cliente
		$client = $this->getWSClient();
		// Constroi os tipos
		$types = $this->build_types($client->__getTypes());
		// Constroi as funcoes
		$functions = $this->build_functions($client->__getFunctions());
		
		// Faz o casamento funcao <> tipos
		foreach ($functions as $fname => $ftype) {
			if(!isset($types[$ftype])){
				if(isset($this->primaryTypes[$ftype])){
					$types[$ftype] = $this->primaryTypes[$ftype];
				} else {
					$types[$ftype] = NULL;
				}
			}
			
			$functions[$fname] = $types[$ftype];
			// $functions[$fname] = isset($types[$ftype]) ? $types[$ftype]:array();
		}

		// Armazena na variavel principal
		$this->available_functions = $functions;
	}
}