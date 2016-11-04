<?php 

namespace Mkny\Front;
use DB;
/**
* 
*/
class FrontLayer
{
	private $debug=false;
	
	function __construct()
	{
		# code...
	}

	/**
	 * Funcao para buscar registros, otimizada para a camada de visao (um facilitador)
	 * 
	 * @param  string $mcs   ModelConfigString - String de configuracao do Model. Atualmente funciona das seguintes formas:
	 *
	 * » ($city = Front::model('cidade(cod_estados)', 5252)[0];echo "{$city->nom_cidade} / {$city->cod_estados->ind_sigla} - {$city->cod_estados->cod_paiss->nom_pais}";)
	 * » {!! Front::model('ficha(softwares,contatos)[cod_ficha, dta_cadastro]', 100)[0]->toArray() !!}
	 * 
	 * @param  int $id    Id para buscar um registro especifico
	 * @param  int $limit Limitador, para a busca
	 * @return \Illuminate\Eloquent\Model[]        Array de modelos
	 */
	public function model($mcs, $id=null, $limit=null)
	{
		$arrModels = array();

		// Verifica se nao foi solicitado mais de um model
		$models = explode('|', $mcs);

		// Tratamento para cada model
		foreach ($models as $model) {
			// Verifica a variavel global do debug
			if($this->debug){
				DB::listen(function($query){
					echo '<br>';
					echo ($query->sql)."\n\n\n";
				});
			}

			// Define-se os campos e os "with"
			$fields = null;
			$with = null;

			// Secao dos "with"
			if(preg_match('/\((.*)\)/', $model, $withs)){
				$with = explode(',',$withs[1]);
				$model = str_replace($withs[0], '', $model);
			}

			// Secao do seletor de campos
			if(preg_match('/\[(.*)\]/', $model, $fields_data)){
				$fields = explode(',', $fields_data[1]);
				$model = str_replace($fields_data[0], '', $model);
			}


			$model = app()->make("\App\Models\\{$model}");


			if($with){
				$model = $model->with($with);
			}

			if($limit){
				$model = $model->take($limit);
			}

			if($id){
				$model = $model->find($id);
			} else {
				$model = $model->get($fields);
			}

			$arrModels[] = $model;
		}

		return $arrModels;
	}

	public function datagrid($mcs, $limit=10)
	{
		// $this->debug=true;

		$datas = array_map(function($x){
			return $x->toArray();
		}, $this->model($mcs, null, $limit));

		// mdd($datas);
		
		$arrTables = array();
		foreach ($datas as $data) {
			$arrTables[] = app()->make('\Mkny\Cinimod\Logic\TableLogic')->setRows($data)->getTable();
		}
		return \Html::tag('div', $arrTables);
	}
}