@if (count($errors->all()))
<div class="alert alert-danger">
	<ul>
		@foreach($errors->all() as $error)
		<li>{{ $error }}</li>
		@endforeach
	</ul>
</div>
@endif

<form class="form-horizontal col-md-12" method="post" action="{{$action or ''}}">
	{{ csrf_field() }}
	@foreach ($data as $field_config)
	
	<div class="form-group col-md-12">
		<!-- <div class="checkbox col-md-6">
			<label>
				<input class="manager-checkbox" name="table[]" type="checkbox" value="">
				$fields
			</label>
		</div> -->
		<div class="col-md-2">
			<label for="{{$field_config['name']}}">{{ trans($controller.'.'.$field_config['name'].'_form') }}{{$field_config['required'] ? '*':''}}</label>
		</div>
		@if (in_array($field_config['type'], array('select', 'status')))
			<div class="col-md-10">
				<select name="{{$field_config['name']}}" id="" class="form-control" data-value="{{$field_config['default_value'] or old($field_config['name'])}}">
					<option value="">- Selecione -</option>
					@if (isset($field_config['values']) && $field_config['values'])
						
						@foreach ( $field_config['values'] as $key => $fcv )
						<?php $transFields = trans($controller.'.'.$field_config['name'].'_form_values'); ?>
						<option value="{{$fcv}}">{{is_array($transFields) ? $transFields[$fcv]:$fcv}}</option>
						@endforeach
					@elseif (isset($field_config['relationship']) && $field_config['relationship'])
						
						<?php $dataModel = $field_config['relationship']['model']::relation($field_config['relationship']); ?>
						@foreach ($dataModel as $dm)
							<option value="{{$dm['id']}}">{{$dm['name']}}</option>
						@endforeach
					@endif
				</select>
			</div>
		@else
		<div class="col-md-10">
			<input type="text" name="{{$field_config['name']}}" class="form-control" placeholder="{{ trans($controller.'.'.$field_config['name'].'_form') }}" value="{{$field_config['default_value'] or old($field_config['name'])}}" />
		</div>
		@endif
	</div>
	@endforeach
	<button type="submit" class="btn btn-success">{{trans($controller.'.button_save')}}</button>
</form>

