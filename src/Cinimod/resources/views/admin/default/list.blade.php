@extends('cinimod.layout.admin')

@section('conteudo')
<div class="row default-admin">
	<h1 class="jumbotron">{{$data['title'] or 'List'}}</h1>

	@if (session('status'))
	<div class="alert alert-{{session('status')}}">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		{{session('message')}}
	</div>
	@endif

	<div>
		<a title="{{ trans($data['controller'].'.button_new') }}" href="{{action($data['controller'].'Controller@getAdd')}}">
			<button type="button" class="btn btn-primary">
				<span class="glyphicon glyphicon-file"></span> {{trans($data['controller'].'.button_new')}}
			</button>

		</a>
	</div>
	<!-- This will be replaced! -->
	@if (count($data['grid']->items()))
	<!-- This will be replaced! -->
	
	<div class="table-responsive">
		<table class="table table-striped check-all-container">
			<!-- <table class="table table-hover table-striped table-bordered check-all-container"> -->
			<thead>
				<tr>
					<th>
						<input type="checkbox" value="" name="id_sec[]" class="checkbox check-all">
					</th>
					<?php $i=0; ?>
					@foreach($data['fields'] as $gridColumnHeader => $field_config)
					<th>
						<a href="?order={{ $i++ }}&card={{ $data['card'] }}">{{ trans($data['controller'].'.'.$gridColumnHeader.'_grid') }}</a>
						<!-- <span>{{ trans($data['controller'].'.'.$gridColumnHeader.'_grid') }}</span> -->
					</th>
					@endforeach
					<!-- Ações title -->
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['grid'] as $gridLine){
					$gridData = $gridLine->toArray();
					$id = array_values($gridData)[0];

					?>
					<tr data-rowid="{{$id}}">
						<td>
							
							<input type="checkbox" value="{{$id}}" name="id_sec[]" class="checkbox">
							
						</td>
						@foreach($data['fields'] as $field_name => $field_config)
						<td>
							@if ($field_config['type'] == 'select' && $field_config['relationship'])
							@if (isset($field_config['relationship']) && $value = $gridLine->{$field_name}()->first())
							<span>{{ $value->{$field_config['relationship']['field_show']} }}</span>
							@else 
							<span>{{$gridData[$field_name]}}</span>
							@endif
							@else
							<span><?=$gridLine->present()->{$field_name}() ?></span>
							@endif
						</td>
						@endforeach
						<!-- Coluna de ações -->
						<td class="col-md-2">
							<a title="{{ trans($data['controller'].'.button_edit') }}" role="button" href="{{action($data['controller'].'Controller@getEdit', ['id' => $id])}}" class="glyphicon glyphicon-edit btn btn-success"></a>
							@if (isset($gridLine['ind_status']))
							<a title="{{ trans($data['controller'].'.button_status_'.($gridLine['ind_status'] == 'A'?'disable':'enable')) }}" role="button" href="{{action($data['controller'].'Controller@getSwitchStatus', ['id' => $id])}}" class="glyphicon glyphicon-{{ $gridLine['ind_status'] == 'A' ? 'ban-circle':'ok' }} btn btn-{{ $gridLine['ind_status'] == 'A' ? 'warning':'info' }}"></a>
							@endif
							<a title="{{ trans($data['controller'].'.button_delete') }}" role="button" href="{{action($data['controller'].'Controller@getDelete', ['id' => $id])}}" class="glyphicon glyphicon-trash btn btn-danger"></a>
						</td>
					</tr>
					<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<th>
							<input type="checkbox" value="" name="id_sec[]" class="checkbox check-all">
						</th>
						@foreach($data['fields'] as $gridColumnHeader => $field_config)
						<th>
							<span>{{ trans($data['controller'].'.'.$gridColumnHeader.'_grid') }}</span>
						</th>
						@endforeach
						<!-- Ações title -->
						<th>Actions</th>
					</tr>
				</tfoot>
			</table>
		</div>
		@endif
		<div class="row-fluid">
			<div class="col-md-2">Total ({{$data['grid']->total()}})</div>
			<div class="col-md-10">
				<div class="text-right">

					{{ $data['grid']->links() }}
				</div>
			</div>

		</div>
	</div>
	@stop
