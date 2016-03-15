@extends('cinimod::layout.admin')

@section('conteudo')
<h1 class="jumbotron">Model-Generator / Configurator - ({{ $controller or 'Default' }})</h1>

<form class="form-horizontal col-md-12" method="post" action="">
	<!-- Auth field -->
	{!! csrf_field() !!}
	
	<!-- Each field -->
<script type="text/javascript">
	function recount(){
		$('.input-count').val(function(k,v){
			return k;
		});
	}
</script>
	
	<table class="table table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Type</th>
				<th>Form</th>
				<th>Grid</th>
				<th>Required</th>
				<th>Searchable</th>
				
			</tr>
		</thead>
		<tbody>
			@foreach ($data as $field_config)
			<tr>

				<td>
					<a href="javascript:;" onclick="var e = $(this).closest('tr');e.prev('tr').before(e);recount();">up</a>
					<br>
					<a href="javascript:;" onclick="var e = $(this).closest('tr');e.next('tr').after(e);recount();">down</a>
					{!! Form::hidden($field_config['name'].'[order]', '', ['class' => 'input-count']) !!}
				</td>
				<td>{{$field_config['name']}}</td>
				<td>
					{!! Form::select($field_config['name'].'[type]', $field_config['types'], $field_config['type'], ['class' => 'form-control']) !!}
				</td>
				<td>
					<div class="checkbox">
						<label>
							{!! Form::hidden($field_config['name'].'[form]', 0) !!}
							{!! Form::checkbox($field_config['name'].'[form]', '1', $field_config['form'], ['class' => '']) !!} Enable
						</label>
					</div>
				</td>
				<td>
					<div class="checkbox">
						<label>
							{!! Form::hidden($field_config['name'].'[grid]', 0) !!}
							{!! Form::checkbox($field_config['name'].'[grid]', '1', $field_config['grid'], ['class' => '']) !!} Enable
						</label>
					</div>
				</td>
				<td>
					<div class="checkbox">
						<label>
							{!! Form::hidden($field_config['name'].'[required]', 0) !!}
							{!! Form::checkbox($field_config['name'].'[required]', '1', $field_config['required'], ['class' => '']) !!} Enable
						</label>
					</div>
				</td>
				<td>
					<div class="checkbox">
						<label>
							{!! Form::hidden($field_config['name'].'[searchable]', 0) !!}
							{!! Form::checkbox($field_config['name'].'[searchable]', '1', $field_config['searchable'], ['class' => '']) !!} Enable
						</label>
					</div>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<div class="col-md-2">
		<button type="submit" class="btn btn-success">Salvar</button>
	</div>
</form>
@stop