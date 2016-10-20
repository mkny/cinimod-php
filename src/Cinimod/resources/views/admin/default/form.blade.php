@if (count($errors->all()))
<div class="alert alert-danger">
	<ul>
		@foreach($errors->all() as $error)
		<li>{{ $error }}</li>
		@endforeach
	</ul>
</div>
@endif

{{$form['head']}}
	@foreach ($form['fields'] as $field_name => $form_field)
		<div class="form-group col-md-12">
			<div class="row {{$field_name}}">
				<div class="col-md-3">{{$form_field['label']}}</div>
				<div class="col-md-9">{{$form_field['field']}}</div>
			</div>
		</div>
	@endforeach
	{{$form['submit']}}
{{$form['foot']}}
