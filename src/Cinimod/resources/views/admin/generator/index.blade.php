@extends('cinimod::layout.admin')



@section('conteudo')

<h1 class="jumbotron">Model-Generator</h1>

@if (session('status'))
<div class="alert alert-{{session('status')}}">
	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
	{{session('message')}}
</div>
@endif

<div class="form-group col-md-5">
	<form action="?" class="form-horizontal" method="get">
		<div class="row">
			<div class="form-group col-md-5">
				<label for="">Conexão</label>
			</div>
			<div class="form-group col-md-5">
				<select name="banco" id="" class="form-control">
					<option value="">Default</option>
					@foreach ($connections as $conn)
					<option {{ $conn === $database ? 'selected="selected"':'' }} value="{{$conn}}">{{$conn}}</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="row">
			<div class="form-group col-md-5">
				<label for="">Schema</label>
			</div>
			@if (count($schemas) > 0)
			<div class="form-group col-md-5 checkbox">
				<ul class="list-unstyled">
					@foreach ($schemas as $schema)
					<li>
						<label>

							<input {{ in_array($schema, $schemas_selected)?'checked="checked"':'' }} type="checkbox" value="{{$schema}}" name="schema[]"> {{$schema}}
						</label>
					</li>
					@endforeach
				</ul>
			</div>
			@endif
		</div>
		<div class="row">
			<button class="btn btn-info">Filtrar</button>
		</div>
	</form>
</div>
<form class="check-all-container" method="post" action="?">
	

	<input type="hidden" name="banco" value="{{$database}}">
	{{ csrf_field() }}
	<table class="table table-striped">
		<tr>
			<th>
				<input type="checkbox" class="check-all" name="" value=""> Todos
			</th>
			<th>Table Name</th>
			<th>Relations</th>
			<th>Controller Name</th>
			<th>Actions</th>
		</tr>

		@foreach ( $tables as $table )
		<tr>
			<td>
				<label>
					<input class="manager-checkbox" name="table[]" type="checkbox" value="{{$table->schema}}.{{$table->name}}" {{ ($table->is_generated) ? 'disabled':''}} >

				</label>
			</td>
			<td>{{$table->schema}}.{{$table->name}}</td>
			<td>
				<small>
					@if ($table->relation) 
					<p>Relations</p> 
					<ol>
						@foreach ($table->relation as $rel)
						<li>
							<p>{{$rel}}</p>
						</li>
						@endforeach
					</ol>
					@endif
				</small>
			</td>
			<td>
				<input type="text" name="controller[]" class="form-control" placeholder="Controller name" value="{{$table->controller}}" disabled />
			</td>
			<td>
				@if ($table->is_generated)
				<p class="text-right">
					<a href="{{ route('adm::gen::del', [$table->controller]) }}" onclick="return confirm('Deseja realmente excluir o item?');">Deleter</a>
				</p>
				@endif
			</td>
		</tr>

		@endforeach
	</table>

	<button type="submit" class="btn btn-success">Gerar</button>
</form>
@stop
