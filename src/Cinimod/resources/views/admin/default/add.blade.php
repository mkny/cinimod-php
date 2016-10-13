@extends('cinimod::layout.admin')
@section('conteudo')

<div class="row-fluid default-admin">
	<div class="col-md-10 col-md-offset-1">
		<div class="row">
			<h1 class="jumbotron">{{trans($controller.'.title_add')}}<p>{{ trans($controller.'.title_add_subtitle') }}</p></h1>
			@include('cinimod::admin.default.form', ['form' => $form])
		</div>
	</div>
</div>

@stop
