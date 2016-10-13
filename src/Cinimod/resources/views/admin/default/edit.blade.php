@extends('cinimod::layout.admin')
@section('conteudo')

<div class="row-fluid default-admin">
	<div class="col-md-10 col-md-offset-1">
		<div class="row">
			<h1 class="jumbotron">{{trans($controller.'.title_edit')}}<p>{{ trans($controller.'.title_edit_subtitle') }}</p></h1>
			@if (session('status'))
			<div class="alert alert-{{session('status')}}">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				{{session('message')}}
			</div>
			@endif
			@include('cinimod::admin.default.form', ['form' => $form])
		</div>
	</div>
</div>

@stop

