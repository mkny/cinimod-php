<?php

namespace Mkny\Cinimod\Logic;



/**
 * Classe Report Logic
 * 
 * @usage
	$d = \App\Models\Questao::orderBy('des_questao')
	->join('sistema.tab_opcao', 'tab_questao.cod_questao', '=' ,'tab_opcao.cod_questao')
	->groupBy('des_questao')
	->get([
		'des_questao',
		DB::raw('count(0)::int AS qtd_opcoes')
		])
	->toArray()
	;

	$r->setHeaders([
		'Questao' => 'string',
		'Qtd Opcoes' => 'number'
		]);
	$r->setBody($d);
	$r->pie('chart');
 */
class ReportLogic {
	private $options = [];
	private $headers;
	private $body;

	/**
	 * Construtor da classe
	 * 
	 */
	public function __construct(){
		// parent::__construct();

		// Estes scripts devem ser carregados diretamente na view
		UtilLogic::addViewVar('scripts', ['https://www.gstatic.com/charts/loader.js']);
		UtilLogic::addViewVar('scripts_static', ["google.charts.load('current', {'packages':['corechart', 'line', 'table']});"]);
	}

	/**
	 * Setta um array de cabeçalhos no Report
	 * 
	 * @param array $value Array com os dados
	 */
	public function setHeaders($value)
	{
		if(!is_array($value) || !empty($this->headers)){
			return;
		}
		$this->headers = $value;
	}

	/**
	 * Retorna os headers do Report
	 * @return array 
	 */
	public function getHeaders()
	{
		// Busca os headers default
		$headers = $this->headers;

		// Se nao existirem headers ele trata a requisição
		if(!count($headers)){
			// Busca o primeiro indice do "body"
			// Faz o tratamento de tipos
			$headers = array_map(function($arr){
				$type = gettype($arr);
				if($type == 'integer'){
					$type = 'number';
				}
				return $type;
			}, $this->getBody()[0]);
		}
		
		return $headers;
	}

	/**
	 * Retorna os headers pre-formatados, conforme a requisicao
	 * 
	 * @param  string $type Formato de retorno
	 * @return array       Dados formatados
	 */
	public function getHeadersFormat($type='normal')
	{
		// Busca todos os headers
		$headers = $this->getHeaders();
		switch($type){
			// Caso seja uma pre-definicao de classe, ja retorna os dados formatados
			case 'normal':
			$arrHeaders = [];
			foreach ($headers as $hName => $hFormat) {
				$arrHeaders[] = "data.addColumn('{$hFormat}', '{$hName}');";
			}
			$headers = implode("\n", $arrHeaders);
			break;
			// Caso seja uma requisicao do 'js', ele formata o array de dados
			case 'js':
			$headers_format = [];
			foreach ($headers as $hName => $hFormat) {
				$headers_format[] = [
				'id' => '',
				'label' => $hName,
				'pattern' => '',
				'type' => $hFormat
				];
			}
			$headers = $headers_format;
			break;
		}

		return $headers;
	}

	/**
	 * Setta o conteudo do relatorio
	 * 
	 * @param array $value Valores
	 */
	public function setBody($value)
	{
		// Se o campo "headers" estiver sendo passado, junto com o array de dados,
		// Ele ja adiciona o headers na variavel da classe
		if (isset($value['headers'])) {
			$h = array_map(function($chain) use ($value){
				$type = gettype($value[0][$chain]);
				if($type == 'integer'){
					return 'number';
				}
				return 'string';
			},array_flip($value['headers']));
			$this->setHeaders($h);
			unset($value['headers']);
		}
		$this->body = $value;
	}

	/**
	 * Retorna o conteudo do relatorio
	 * 
	 * @return array Dados
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Retorna o corpo do relatorio, formatado
	 * 
	 * @param  string $type Tipo de retorno
	 * @return array       Dados formatados
	 */
	public function getBodyFormat($type='normal')
	{
		// Dados do corpo
		$body = $this->getBody();
		$fBody = '';
		switch($type){
			case 'normal':

			$hBody = [];

			foreach ($body as $key => $value) {
				$hBody[] = (array_values($value));
			}

			$fBody = json_encode($hBody);
			break;

			// Caso seja 'js', o corpo ja vai formatado com as chaves adicionais
			case 'js':
			$fBody = array_map(function($arr){
				$arrRet = [];
				if(is_array($arr)){
					foreach ($arr as $arrData) {
						$arrRet[] = ['v' => $arrData, 'f', null];
					}
				} else {
					$arrRet = ['v' => $arr, 'f', null];
				}
				return ['c' => $arrRet];
			}, $body);
			break;
		}

		return $fBody;
	}

	/**
	 * Setta a opcao do Report
	 * 
	 * @param string $optName  Chave
	 * @param mixed $optValue Valor
	 */
	public function setOption($optName, $optValue)
	{
		$this->options[$optName] = $optValue;
	}

	/**
	 * Busca todas as opcoes atribuidas
	 * 
	 * @param  string $value Chave (opcional)
	 * @return array         Dados
	 */
	public function getOptions($value=false)
	{
		return $this->options;
	}

	/**
	 * Cria um grafico do tipo Linhas "Line"
	 * 
	 * @param  string $container id do objeto fornecido
	 * @return void
	 */
	public function line($container)
	{
		// Busca e formata as opcoes
		$options = 'var options = '.json_encode($this->getOptions()).';';

		// Headers formato normal
		$headers = $this->getHeadersFormat();

		// Corpo do report
		$data = ($this->getBodyFormat());

		// Monta o script
		$script = "
		google.charts.setOnLoadCallback(function(){
			var data = new google.visualization.DataTable();
			{$headers}
			data.addRows({$data});
			{$options}
			var chart = new google.charts.Line(document.getElementById('{$container}'));
			chart.draw(data, options);
		}); ";
		UtilLogic::addViewVar('scripts_static', [
			$script
			]);
	}


	/**
	 * Cria um grafico do tipo Barras "Bar"
	 * 
	 * @param  string $container id do objeto fornecido
	 * @return void
	 */
	public function bar($container)
	{
		// Busca e formata as opcoes
		$options = 'var options = '.json_encode($this->getOptions()).';';

		// Headers formato normal
		$headers = $this->getHeadersFormat();

		// Corpo do report
		$data = ($this->getBodyFormat());

		// Monta o script
		$script = "
		google.charts.setOnLoadCallback(function(){
			var data = new google.visualization.DataTable();
			{$headers}
			data.addRows({$data});
			{$options}
			var chart = new google.visualization.BarChart(document.getElementById('{$container}'));
			chart.draw(data, options);
		}); ";
		UtilLogic::addViewVar('scripts_static', [
			$script
			]);
	}


	/**
	 * Cria um grafico do tipo Pizza "Pie"
	 * 
	 * @param  string $container id do objeto fornecido
	 * @return void
	 */
	public function pie($container)
	{
		// Busca e formata as opcoes
		$options = 'var options = '.json_encode($this->getOptions()).';';

		// Headers formato normal
		$headers = $this->getHeadersFormat();

		// Corpo do report
		$data = ($this->getBodyFormat());

		// Monta o script
		$script = "
		google.charts.setOnLoadCallback(function(){
			var data = new google.visualization.DataTable();
			{$headers}
			data.addRows({$data});
			{$options}
			var chart = new google.visualization.PieChart(document.getElementById('{$container}'));
			chart.draw(data, options);
		}); ";
		UtilLogic::addViewVar('scripts_static', [
			$script
			]);
	}
}