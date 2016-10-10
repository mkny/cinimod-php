<?php

namespace Mkny\Cinimod\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

// use Illuminate\Filesystem\Filesystem;
// use Mkny\Cinimod\Logic\ReportLogic;

use DB;
// use GuzzleHttp;
// use Illuminate\Support\HtmlString;
// use Mkny;
// use Mkny\Cinimod\Logic AS Logic;

use Mkny\Cinimod\Logic;

class DashboardController extends Controller
{
	public function anyIndex(){

		

		// return view('unicorn.admin.dashboard');
		// return view('unicorn.pagina');
		return view('cinimod::admin.dashboard');
	}

	public function anyIndexes(Logic\TableLogic $t)
	{
		// $m = new \App\Models\Pessoa();
		// $fields_data = $m->_getConfig('datagrid');
		// $fields = array_keys($fields_data);
		// $d = $m
  //       // ->orderBy($order, \Request::input('card', 'asc'))
  //       ->paginate(
  //           // Qtd de campos
  //           10,
  //           // $limit,
  //           // Pega os fieldnames para o select
  //           $fields
  //           );


		// $t->addTableClass(array(
		// 	'table',
		// 	'table-striped',
		// 	'check-all-container'
		// 	));

		// $headers = array_merge(
		// 	array('checks' => [\Form::checkbox('idx', '', '', ['class' => 'checkbox check-all'])]),
		// 	$fields,
		// 	array('actions')
		// 	);



		// $headersTranslated = [];
		// foreach ($headers as $field_key => $field_name) {
		// 	if(is_numeric($field_key)){
		// 		$headersTranslated[$field_name] = trans(class_basename($m).'.'.$field_name.'_grid');
		// 		if(isset($fields_data[$field_name]['searchable']) && $fields_data[$field_name]['searchable']){
		// 			$headersTranslated[$field_name] = [
		// 			\Html::tag(
		// 				'a',
		// 				$headersTranslated[$field_name],
		// 				['href' => "?order=".array_search($field_name, $fields)."&card=".(\Request::input('card', 'asc') == 'asc' ? 'desc':'asc')]
		// 				)
		// 			];
		// 		}
				
		// 	} else {
		// 		$headersTranslated[$field_key] = $field_name;
		// 	}
		// }

		// $t->setHeaders($headersTranslated);


		// // echo '<pre>';
		// $dataRows = [];
		// foreach ($d->items() as $item) {
		// 	$dataCols = [];
		// 	foreach ($fields as $field_name) {
		// 		$dataCols[$field_name] = $item->present()->{$field_name};
		// 	}
		// 	$dataRows[] = $dataCols;
		// }

		// // print_r($dataRows);
		// // exit;
		// // $d;

		// foreach ($dataRows as $drow) {
		// 	$drow['checks'] = array(
		// 		\Form::checkbox('id_sec[]', $drow[$m->primaryKey], '', ['class'=> 'checkbox'])
		// 		);

		// 	$drow['actions'] = array(
		// 		$t->button(
		// 			'edit/'.$drow[$m->primaryKey],
		// 			'Editar',
		// 			'glyphicon glyphicon-edit btn btn-success'),

		// 		$t->button(
		// 			'switch-status/'.$drow[$m->primaryKey],
		// 			'Editar',
		// 			$drow['ind_status'] == 'A' ? 'glyphicon glyphicon-ban-circle btn btn-warning':'glyphicon glyphicon-ok btn btn-info'),

		// 		$t->button(
		// 			'delete/'.$drow[$m->primaryKey],
		// 			'Editar',
		// 			'glyphicon glyphicon-trash btn btn-danger')
		// 		);

		// 	$t->insertRow($drow);
		// }
		// $t->setRows($d);
		


		return view('cinimod::admin.default_dashboard')->with(['table' => $t->getTable()]);
	}
}

// return view('cinimod.admin.default_dashboard');
/**
 * [title]Cinimod-gen[/title]
 * A complex generator
 * 
 * 
 * *** Features ***
 * 
 * » Webservices
 * Uma forma simples de trabalhar com webservices (SOAP12)
 * 
 * 	» Client (Mkny\Cinimod\Logic\WSClientLogic)
 *  	Ao chamar a classe a mesma cria um ambiente preparado para o 
 *   	consumo de servicos SOAP12
 *  » Server (Mkny\Cinimod\Logic\WSServerLogic)
 *  	Ao chamar esta classe, ela cria um servico automatizado para 
 *   	distribuicao de dados via SOAP12
 * 
 * » Report generator (rewrite)
 * Biblioteca de facilitacao de criacao de charts / reports
 * Gera javascripts para a utilizacao da biblioteca Google Charts
 * 	» mkny.report.js
 *  	Arquivo que comunica com um controlador gerador de charts
 * 
 * 
 * 
 */
