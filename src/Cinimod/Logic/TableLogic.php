<?php

namespace Mkny\Cinimod\Logic;

/**
* TableLogic
*/
class TableLogic
{
	/**
	 * Armazena as classes da tabela
	 * 
	 * @var array
	 */
	private $tableClass = [];

	/**
	 * Armazena os headers
	 * 
	 * @var array
	 */
	private $headers = [];

	/**
	 * Armazena as linhas
	 * 
	 * @var array
	 */
	private $rows = [];

	/**
	 * Adiciona uma classe na tabela
	 * 
	 * @param array|string $classNames Nome da classe ou array de nomes
	 */
	public function addTableClass($classNames)
	{
		// Caso nao esteja sendo fornecido um array, ele forca dando explode nos espacos
		if(!is_array($classNames)){
			$classNames = explode(' ', $classNames);
		}

		$this->tableClass = $classNames;
	}

	/**
	 * Retorna as classes da tabela
	 * 
	 * @return array Classes
	 */
	public function getTableClass()
	{
		return $this->tableClass;
	}

	/**
	 * Setta os headers da tabela
	 * 
	 * @param array $arrHeaders headers traduzidos ou nao
	 */
	public function setHeaders($arrHeaders)
	{
		// Para cada indice, ele chama uma vez o insertheader
		foreach ($arrHeaders as $key => $header) {
			$this->insertHeader($key, $header);
		}
	}

	/**
	 * Retorna todos os headers
	 * 
	 * @return array Headers
	 */
	public function getHeaders()
	{
		$h = $this->headers;
		if(!count($h)){
			$rows = $this->getRows();
			if(count($rows)){
				$h = array_combine((array_keys($rows[0])), (array_keys($rows[0])));
			}
		}

		return $h;
	}

	/**
	 * Setta um header na tabela
	 * 
	 * @param  string $key        Chave para associacao posterior
	 * @param  array $headerData Dados do header
	 * @return void
	 */
	public function insertHeader($key, $headerData)
	{
		$text = $headerData;
		// $config = [];

		// if(is_array($headerData)){
		// 	$config = $headerData;
		// 	$text = $config['trans'];
		// }
		$this->headers[$key] = $text;
	}

	/**
	 * Facilitador para adicao de linhas na tabela
	 * 
	 * @param array $arrData Dados fornecidos
	 */
	public function setRows($arrData)
	{
		foreach ($arrData as $data) {
			$this->insertRow($data);
		}
	}

	/**
	 * Retorna as linhas do corpo da tabela
	 * 
	 * @return array Linhas
	 */
	public function getRows()
	{
		return $this->rows;
	}

	/**
	 * Adiciona uma linha no corpo da tabela
	 * 
	 * @param  array $row Dados associativos para a montagem
	 * @return void
	 */
	public function insertRow($row)
	{
		// Busca os headers, para fazer o filtro dos campos que nao serao tratados
		$arrHeadersKey = array_keys($this->getHeaders());

		$rowFormat = [];
		foreach ($row as $key => $value) {
			// Se o item fornecido, tem um header que nao esta definido, ele ignora
			if(count($arrHeadersKey) && !in_array($key, $arrHeadersKey)){
				continue;
			}
			// Adiciona o item no array
			$rowFormat[$key] = $value;
		}

		$this->rows[] = $rowFormat;
	}

	/**
	 * Retorna os dados da tabela
	 * 
	 * @return string Html da tabela
	 */
	public function getTable()
	{

		// Busca os cabecalhos
		$headers = $this->getHeaders();

		// Armazena o html do head montado
		$headObj = [];
		foreach ($headers as $header){
			$headObj[] = \Html::tag('th', $header);
		}
		// Objeto thead
		$thead = \Html::tag('thead', [\Html::tag('tr', $headObj)]);

		// Busca todas as linhas
		$rows = $this->getRows();

		// Armazena o html do body montado
		$rowsObj = [];
		foreach ($rows as $row) {
			$cols = [];

			// Pega cada header (ideal para filtrar os elementos invalidos, ordenar, etc)
			foreach ($headers as $headerKey => $headerValue) {
				// echo '<pre>';
				// print_r($headers);
				// exit;

				// Tratamento para os botoes montados dinamicamente
				$colData = (is_array($row[$headerKey]) ? $row[$headerKey]:[$row[$headerKey]]);

				// Adiciona no elemento principal
				$cols[] = \Html::tag('td', $colData);
			}

			// Monta a linha
			$rowsObj[] = \Html::tag('tr', $cols);
		}

		// Objeto tbody com as linhas montadas
		$tbody = \Html::tag('tbody', $rowsObj);

		// Escreve a tabela
		return \Html::tag('table', [$thead, $tbody], ['class' => implode(' ', $this->getTableClass())]);
	}

	/**
	 * Facilitador para criacao de botoes
	 * 
	 * @param  string $link  Url
	 * @param  string $title Titulo
	 * @param  string $class Classe do botao
	 * @param  string $html  Texto interno
	 * @return \Html        Objeto botao
	 */
	public function button($link='javascript:;', $title='', $class='', $html='')
	{
		return \Html::tag('a', '', [
			'class' => $class,
			'href' => $link,
			'role' => 'button',
			'title' => $title]);
	}
}

