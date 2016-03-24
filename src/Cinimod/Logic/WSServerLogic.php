<?php

namespace Mkny\Cinimod\Logic;

ini_set('soap.wsdl_cache_enabled', '0');

// Chama a biblioteca do Zend
use Zend\Soap;

/**
* Cria um WebService Server, em SOAP
* 
* @usage
* 
$wss->init();
$wss->setClass('\App\Services\TreinoService');
$wss->handle();
*/
class WSServerLogic
{
	/**
	 * Url do WS
	 * @var string
	 */
	private $ws_url;

	/**
	 * Classe controlada pelo WS
	 * @var string
	 */
	private $ws_class;

	/**
	 * Inicializador da classe
	 * 
	 * @param  string $wsUrl Url do WS
	 * @return xml|WS
	 */
	public function init($wsUrl=false)
	{
		// Se nao estiver passando a url, ele tenta fornecer baseado no local do script
		// Aviso: Isso nao e recomendado!
		if(!$wsUrl){
			$wsUrlMake = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
			$wsUrlParts = parse_url($wsUrlMake);
			$wsUrl = "http://{$wsUrlParts['host']}{$wsUrlParts['path']}";
		}

		// Atribuia  url para a classe
		$this->ws_url = $wsUrl;
	}

	/**
	 * Setta a classe que sera utilizada pelo WS
	 * 
	 * @param object $class Classe do WS
	 */
	public function setClass($class='')
	{
		$this->ws_class = $class;
	}

	/**
	 * Faz o tratamento da exibicao
	 * 
	 * @return xml|ws Retorna baseado no parametro WSDL
	 */
	public function handle()
	{
		// Se estiver sendo passado o parametro "wsdl" exibe a definicao
		if (isset($_GET['wsdl'])) {
			return $this->handleWSDL();
		} else {
			// Caso contrario, inicia o servidor SOAP
			return $this->handleSOAP();
		}
	}

	/**
	 * Exibe o WSDL Description
	 * @return xml WSDL
	 */
	private function handleWSDL()
	{
        // Objeto do Zend para leitura da classe
		$autodiscover = new Soap\AutoDiscover(new \Zend\Soap\Wsdl\ComplexTypeStrategy\ArrayOfTypeComplex());

		// Informa a classe
		$autodiscover->setClass($this->ws_class);

        // Informa a url do WS
		$autodiscover->setUri($this->ws_url);
		// Gera
		$wsdl = $autodiscover->generate();
		$wsdl = $wsdl->toDomDocument();

        // Manda um cabecalho de XML
		@header('content-type:text/xml');

		// Escreve em XML
		echo $wsdl->saveXML();
		exit;
	}

	/**
	 * Starta o WS Server
	 * @return SoapServer Abre o servico
	 */
	private function handleSOAP()
	{
		// Instancia so ZendSoapServer
		$soap = new \Zend\Soap\Server($this->ws_url . '?wsdl',array(
//            'cache_wsdl' => WSDL_CACHE_NONE
			));

        // Setta a classe para o servidor
        $soap->setClass($this->ws_class);

        // Starta tudo
        return $soap->handle();
    }
}