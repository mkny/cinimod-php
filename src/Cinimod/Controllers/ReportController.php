<?php

namespace Mkny\Cinimod\Controllers;

// Requester
use Illuminate\Http\Request;
// use App\Http\Requests;
// Controller
use App\Http\Controllers\Controller;
// Logic
use Mkny\Cinimod\Logic\ReportLogic;

// Report related
use DB;
use Illuminate\Filesystem\Filesystem;
use App\Models;

class ReportController extends Controller
{
	/**
	 * Report Logic
	 * 
	 * @var object
	 */
	// private $logic;

	/**
	 * Construtor da classe
	 * 
	 * @param ReportLogic $r
	 */
	// public function __construct(Logic\ReportLogic $r)
	// {
	// 	$this->logic = $r;
	// }

	/**
	 * Index, retorna uma tela simples parametrizada
	 * 
	 * @return void
	 */
	public function getIndex()
	{

		return view('test_chart');
	}

	/**
	 * Funcao usada pelo ajax para buscar o relatorio informando o nome da action
	 * 
	 * @return json Relatorio formatado para o Google Charts
	 */
	public function getGet(ReportLogic $r)
	{
		// Busca o nome do relatorio
		// $report_namespace = $this;
		$report_request = \Request::input('report');

		// Atribui um rows vazio
		$rows = array();

		// Verifica se é mesmo um report request!
		if(substr($report_request, 0,6) === 'report' && method_exists($this, $report_request)){
			$rows = $this->{$report_request}();
		} else {
			// Joga uma exception caso nao exista!
			abort(405,'Inexistente!');
		}

		// Comeca o tratamento do array de configuracoes do Google Charts
		$arrConfig = [];

		// Adiciona as linhas no Logic
		$r->setBody($rows);

		// Montador de colunas
		$arrConfig['cols'] = $r->getHeadersFormat('js');

		// Montador de linhas
		$arrConfig['rows'] = $r->getBodyFormat('js');

		// Retorna o array para o laravel, que ira retornar o json
		return $arrConfig;
	}

	// Relatorios
	
	// Dummy xml request (file-xml)
	private function report_empresas_implantadas(){
		$fs = new Filesystem;
		$xml = json_decode(json_encode(simplexml_load_string($fs->get(storage_path().'/dummy/test-request.xml'))), true);
		$rows = [];
		foreach ($xml['Meses']['Mes'] as $mesData) {
			$columns = [];
			$columns[] = $mesData['NumMes'].' - '.$mesData['NumAno'];
			$rows['headers'] = ['Mês'];
			foreach ($mesData['Territorio'] as $tData) {
				$rows['headers'][] = $tData['Codigo'].'.'.$tData['Descricao'];
				$columns[] = (integer) $tData['QtdEmpresas'];
			}
			$rows[] = $columns;
		}
		return $rows;
	}

	// Dummy report - Database + Geo
	private function report_geo_selling()
	{
		$rows = Models\Cidade::orderBy('nom_cidade')
		// ->where('cod_estado', '=', '13')
		->where('nom_cidade', 'ilike', 'aba%')
		->get([
			'nom_cidade',
			'cod_cidade'
			])
		->toArray();
		return $rows;
	}
	// Dummy report - Database
	private function report_questions_by_category(){
		$rows = Models\Questao::
		join('sistema.tab_categoria AS tc', 'tc.cod_categoria', '=', 'tab_questao.cod_categoria')
		->groupBy('tc.nom_categoria')
		->get([
			DB::raw('tc.nom_categoria AS "Categoria"'),
			DB::raw('count(tab_questao.des_questao)::integer AS "Quantidade"'),
			// DB::raw('10 AS "Quantidade_2"')
			])
		->toArray();
		return $rows;
	}

}
