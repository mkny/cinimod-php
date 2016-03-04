<?php

namespace Mkny\Cinimod\Logic;

// WS Type (soap/rest)


/**
 * Classe para tratamento de WS - Requests
 * 
 * usage:
 * 
 * $ws->init('http://www.predic8.com:8080/material/ArticleService?wsdl');
 * dd($ws->getAll());
 * or
 * $ws->init('http://www.webservicex.com/globalweather.asmx?wsdl');
 * dd($ws->GetCitiesByCountry('Brazil'));
 */
class WSLogic extends MknyLogic {
	// private $ws_url;
	private $ws_client;
	private $ws_types;
	private $ws_methods;


	private $available_functions;

	// private $debug = false;

	public function __construct()
	{
		parent::__construct();
		ini_set('default_socket_timeout', '10');
	}

	/**
	 * Inicializador da classe
	 * 
	 * @param  string $url Url do WS
	 * @return void
	 */
	public function init($url){
		$this->ws_client = @new \SoapClient($url, array(
			// 'soap_version' => SOAP_1_2
			));
		// Implementar debug-trace
		// array('trace' => 1);
		return $this->build();
	}

	/**
	 * Faz a chamada do metodo do webservice
	 * 
	 * @param  string $method Nome do metodo
	 * @param  array $args   Argumentos passados na funcao
	 * @return array         Retorno do metodo WS
	 */
	public function __call($method, $args){
		$methods = $this->getAvailableFunctions();

		// Verifica se o metodo existe no WS
		if (!isset($methods[$method])) {
			abort(400, 'Metodo invalido!');
		} elseif(count($methods[$method]) > 0){
			// Se existem parametros
			$method_parameters = $methods[$method];
			if(count($method_parameters) <> count($args)){
				abort(400, 'Quantidade de parametros nao bate');
			}
			// Combina os parametros solicitados, com os argumentos informados, montando o request
			$params = array_combine($method_parameters,$args);
		} else {
			$params = null;
		}

		// Passa pro tratador, apos efetuar o request no ws, fornecendo os parametros
		return $this->treatRequest($this->getWSClient()->{$method}($params));
	}

	/**
	 * Faz o tratamento do retorno
	 * 
	 * @param  \stdClass $request Retorno da requisicao
	 * @return array             Dados retornados
	 */
	private function treatRequest(\stdClass $request)
	{
		// Define um formato vazio
		$fRequest = '';
		// Pre-formata o request (tirando o primeiro indice inutil que vem sempre)
		$pre = array_values((array) $request)[0];
		
		// Faz o tratamento, pelo tipo de variavel !important
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

		return $fRequest;
	}

	/**
	 * Retorna o objeto ws_client
	 * 
	 * @return SoapClient Objeto cliente
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
			$arrFunctions[$f_parts[0]] = $f_parts[1];
		}

		return $this->ws_methods = $arrFunctions;
	}

	/**
	 * Constroi as func-types (parametros de funcao do metodo)
	 * @param  array $types Array de tipos informados
	 * @return array        Array de tipos tratados
	 */
	private function build_types($types)
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
		}

		return $this->ws_types = $arrTypes;
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
			$functions[$fname] = $types[$ftype];
		}

		// Armazena na variavel principal
		$this->available_functions = $functions;
	}



}