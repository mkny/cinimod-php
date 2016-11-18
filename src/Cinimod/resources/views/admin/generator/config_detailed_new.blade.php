@extends('cinimod::layout.admin')

@section('conteudo')


<h1 class="jumbotron">Model-Generator / Configurator - ({{ $controller or 'Default' }})</h1>

<form class="form-horizontal col-md-12" method="post" action="">
	<!-- Auth field -->
	{!! csrf_field() !!}
	
	<!-- Each field -->
	<ol>
		<!-- <li>Select all form</li>
		<li>Select all grid</li> -->
		<li><a href="javascript:;" onclick="selectFormRequired();">Select form required's</a></li>
		<li><a href="javascript:;" onclick="selectGridRequired();">Select grid required's</a></li>
		<li><a href="javascript:;" onclick="recount();">Reorder</a></li>
		<li>
			<a href="javascript:;" onclick="addDynamicField('.table-list-fields > tbody > tr:eq(0)')">Add new field (dynamic)</a>
		</li>
	</ol>
	<table class="table table-striped table-list-fields">
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Type</th>
				<th>Form</th>
				<th>Grid</th>
				<th>Required</th>
				<!-- <th>Relationship</th> -->
				<th>Sortable</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($data as $field_config)
			<tr class="dragorder">
				<td>
					<a href="javascript:;" onclick="var e = $(this).closest('tr');e.prev('tr').before(e);recount();">up</a>
					<br>
					<a href="javascript:;" onclick="var e = $(this).closest('tr');e.next('tr').after(e);recount();">down</a>
					{!! Form::text($field_config['name'].'[order]', $field_config['order'], ['class' => 'input-count']) !!}
				</td>
				<td>
					{{$field_config['name']}}
					{!! Form::hidden($field_config['name'].'[name]', $field_config['name']) !!}

				</td>
				<td>
					{!! Form::select($field_config['name'].'[type]', $field_config['types'], $field_config['type'], ['class' => 'form-control']) !!}
					@if (isset($field_config['relationship']) && $field_config['relationship'])
					
					<!-- 	<a href="javascript:;" onclick="$(this).next().toggleClass('hidden');">Relations</a> -->
					
					<table class="table table-bordered">
						<tr>
							<td>depends</td>
							<td>
								{!! Form::text(
								$field_config['name'].'[relationship][dependsOn]',
								isset($field_config['relationship']['dependsOn'])?$field_config['relationship']['dependsOn']:null,
								['class' => 'form-control']
								)
								!!}
							</td>
						</tr>
						<tr>
							<td>model</td>
							<td>
								{!! Form::text(
								$field_config['name'].'[relationship][model]',
								isset($field_config['relationship']['model'])?$field_config['relationship']['model']:null,
								['class' => 'form-control']
								)
								!!}
							</td>
						</tr>
						<tr>
							<td>field_key</td>
							<td>
								{!! Form::text(
								$field_config['name'].'[relationship][field_key]',
								isset($field_config['relationship']['field_key']) ?$field_config['relationship']['field_key']:null,
								['class' => 'form-control']
								) !!}
							</td>
						</tr>
						<tr>
							<td>field_fkey</td>
							<td>
								{!! Form::text(
								$field_config['name'].'[relationship][field_fkey]',
								isset($field_config['relationship']['field_fkey']) ?$field_config['relationship']['field_fkey']:null,
								['class' => 'form-control']
								) !!}
							</td>
						</tr>
						<tr>
							<td>field_show</td>
							<td>
								{!! Form::text(
								$field_config['name'].'[relationship][field_show]',
								isset($field_config['relationship']['field_show']) ?$field_config['relationship']['field_show']:null,
								['class' => 'form-control']
								) !!}
							</td>
						</tr>
						<tr>
							<td>where</td>
							<td>
								<!-- [relationship][where] -->
								{!! Form::text($field_config['name'].'[relationship][where][]', '', ['class' => 'form-control']) !!}
							</td>
						</tr>
					</table>
					
					@endif
				</td>
				<td>
					<div class="checkbox">
						{!! Form::hidden($field_config['name'].'[form_add]', 0) !!}
						{!! Form::hidden($field_config['name'].'[form_edit]', 0) !!}
						<label>
							{!! Form::checkbox($field_config['name'].'[form_add]', '1', $field_config['form_add'], ['class' => 'input-select-form']) !!} Enable add
						</label>
						<label>
							{!! Form::checkbox($field_config['name'].'[form_edit]', '1', $field_config['form_edit'], ['class' => 'input-select-form']) !!} Enable edit
						</label>
					</div>
				</td>
				<td>
					<div class="checkbox">
						<label>
							{!! Form::hidden($field_config['name'].'[grid]', 0) !!}
							{!! Form::checkbox($field_config['name'].'[grid]', '1', $field_config['grid'], ['class' => 'input-select-grid']) !!} Enable
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
				<!-- <td>
					<div class="checkbox">
						<label>
							{!! Form::hidden($field_config['name'].'[relationship]', 0) !!}
							{!! Form::checkbox($field_config['name'].'[relationship]', '1', $field_config['relationship'], ['class' => '']) !!} Enable
						</label>
					</div>
				</td> -->
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


