@if (count($errors->all()))
<div class="alert alert-danger">
	<ul>
		@foreach($errors->all() as $error)
		<li>{{ $error }}</li>
		@endforeach
	</ul>
</div>
@endif

@foreach ($form as $form_field)
	{{$form_field}}
@endforeach
