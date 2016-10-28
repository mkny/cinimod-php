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

	// public function modelGet($ModelName, $id, $Fields=null)
	// {
	// 	return app()->make("\App\Models\\$ModelName")->find($id);
	// }


	public function model($ModelName, $id=null, $limit=null)
	{
		$arrModels = array();

		$models = explode('|', $ModelName);
		foreach ($models as $model) {
			if($this->debug){
				DB::listen(function($query){
					echo ($query->sql)."\n\n\n";
				});
			}

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

	public function datagrid($ModelName, $limit=10)
	{
		// $this->debug=true;

		$datas = array_map(function($x){
			return $x->toArray();
		}, $this->model($ModelName, null, $limit));

		// mdd($datas);
		
		$arrTables = array();
		foreach ($datas as $data) {
			$arrTables[] = app()->make('\Mkny\Cinimod\Logic\TableLogic')->setRows($data)->getTable();
		}
		return \Html::tag('div', $arrTables);
	}
}