@extends('cinimod::layout.admin')

@section('conteudo')
<h1 class="jumbotron">Model-Generator / Configurator - ({{ $controller or 'Default' }})</h1>

<form class="form-horizontal col-md-12" method="post" action="">
	<!-- Auth field -->
	{!! csrf_field() !!}

	<!-- Each field -->
	@foreach ($data as $field_config_name => $field_config)
	<div class="form-group col-md-12">
		<!-- Fieldname -->
		<div class="cinimod-field-name col-md-2">
			<label for="">{{$field_config_name}}</label>
		</div>

		<!-- Field variables -->
		<div class="col-md-10">
			@foreach ($field_config as $field_config_type => $field_config_values)
			<?php
			// Exclui alguns tipos de campos do objeto!
			if (in_array($field_config_type, array('types', 'values','relationship', 'name'))){
				continue;
			}
			?>
			<div class="col-md-2">
				<label for="{{$field_config_name}}[{{$field_config_type}}]">{{$field_config_type}}</label>
			</div>
			<div class="col-md-10">
				@if (gettype($field_config_values) == 'string')
					@if ($field_config_type == 'type')
					<select name="<?php echo "{$field_config_name}[{$field_config_type}]" ?>" id="{{$field_config_name}}[{{$field_config_type}}]" data-value="{{$field_config_values}}" class="form-control">
						@foreach ($field_config['types'] as $type_field)
						<option value="{{$type_field}}">{{$type_field}}</option>
						@endforeach
					</select>
					@else
					<input id="{{$field_config_name}}[{{$field_config_type}}]" type="text" name="<?php echo "{$field_config_name}[{$field_config_type}]" ?>" class="form-control" placeholder="{{$field_config_values}}" value="" />	
					@endif
				@elseif(gettype($field_config_values) == 'boolean')
				<select data-value="{{(integer) $field_config_values}}" name="<?php echo "{$field_config_name}[{$field_config_type}]" ?>" id="{{$field_config_name}}[{{$field_config_type}}]" class="form-control">
					<option value="0">false</option>
					<option value="1">true</option>
				</select>
				@elseif(is_array($field_config_values))

				@else
				{{gettype($field_config_values)}}
				@endif
			</div>
			@endforeach
		</div>
		<!-- Field variables end -->
	</div>
	@endforeach
	<div class="col-md-2">
		<button type="submit" class="btn btn-success">Salvar</button>
	</div>
</form>
@stop