@extends('layout.admin')

@section('conteudo')
<h1 class="jumbotron">Model-Generator / Configurator - ({{ $controller or 'Default' }})</h1>

<form class="form-horizontal col-md-12" method="post" action="">
	{!! csrf_field() !!}
	@foreach ($data as $field_name => $field)
	<div class="form-group col-md-12">
		<div class="col-md-2">
			<label for="">{{$field_name}}</label>
		</div>
		<div class="col-md-10">
			@foreach ($field as $field_cfg => $field_config)
			<?php if (in_array($field_cfg, array('types', 'values','relationship', 'name'))){continue;} ?>
			
			<?php if ($field_cfg == 'relationship' && !is_array($field_config)){continue;} ?>
			<div class="col-md-2">{{$field_cfg}}</div>
			<div class="col-md-10">
				@if (gettype($field_config) == 'string')
				@if ($field_cfg == 'type')
				<select name="<?php echo "{$field['name']}[{$field_cfg}]" ?>" id="" data-value="{{$field_config}}" class="form-control">
					@foreach ($field['types'] as $type)
					<option value="{{$type}}">{{$type}}</option>
					@endforeach
				</select>
				@else
				<input type="text" name="<?php echo "{$field['name']}[{$field_cfg}]" ?>" class="form-control" placeholder="{{$field_config}}" value="" />	
				@endif
				@elseif(gettype($field_config) == 'boolean')
				<select data-value="{{(integer) $field_config}}" name="<?php echo "{$field['name']}[{$field_cfg}]" ?>" id="" class="form-control">
					<option value="0">false</option>
					<option value="1">true</option>
				</select>
				@elseif(is_array($field_config))

				@else
				{{gettype($field_config)}}
				@endif
			</div>
			@endforeach
		</div>
	</div>
	@endforeach
	<div class="col-md-2">
		<button type="submit" class="btn btn-success">Salvar</button>
	</div>
</form>
@stop