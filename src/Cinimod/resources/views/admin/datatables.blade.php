@extends('layout.admin')

@section('conteudo')

<table class="table table-striped table-bordered tableSorter">
	<thead>
		<tr>
			@foreach($config as $cfg)
			<th>{{$cfg}}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		<tr>
			@foreach($config as $cfg)
			<td title="{{$cfg}}"></td>
			@endforeach
		</tr>
	</tbody>
	<tfoot>
		<tr>
			@foreach($config as $cfg)
			<th>{{$cfg}}</th>
			@endforeach
		</tr>
	</tfoot>
</table>

<!-- 


	public function getIndex2()
	{
		\App\Logic\UtilLogic::addViewVar('scripts', [
			'/javascripts/jquery.tablesorter.min.js',
			'/javascripts/jquery.dataTables.js',
			'/javascripts/dataTables.bootstrap.js',
			'/javascripts/test.js',
			]);

		// $data['data']= $this->model->all();
		$data['config'] = array_keys($this->model->_getConfig('datagrid'));
		return view('admin.datatables')->with($data);
	}

	public function postDatasource()
	{
		$config = array_keys($this->model->_getConfig('datagrid'));

		// $orderBy = array_map(function($arrx) use ($config){
		// 	$arr = array_values($arrx);
		// 	return $config[$arr[0]].' '.$arr[1];
		// }, \Request::input('order', ''));

		$where = array_filter(array_map(function($colData) use ($config){
			if (!$colData['search']['value']) {
				return false;
			}
			return $config[$colData['data']]." ilike '%".str_replace(' ', '%', $colData['search']['value'])."%'";
		}, \Request::input('columns')));
		
		$limit = \Request::input('length', 10);
		$offset = \Request::input('start', 0)+1;
		$searchGlobal = \Request::input('search.value');


		// $currentPage = 0;
	    $currentPage = ceil($offset / $limit);
	    // var_dump($currentPage);

		// force current page to 5
		Paginator::currentPageResolver(function() use ($currentPage) {
			// return 0;
		    return $currentPage;
		});

		$ds = $this->model;
		foreach ($where as $w_data) {
			$ds = $ds->where(DB::raw($w_data));
		}

        
        $ds = $ds->paginate(
            // Qtd de campos
            $limit,
            // Pega os fieldnames para o select
            $config
            );//->appends(\Request::only(['order', 'card']));

        $data = array_map(function($item){
        	return array_values($item->toArray());
        }, $ds->items());
        
        return [
		'sEcho'=>0,
		'iTotalRecords' => $ds->total(),
		'iTotalDisplayRecords' => $ds->total(),
		'data' => $data
		];
	}

	use DB;
use Illuminate\Pagination\Paginator;
 -->

@stop