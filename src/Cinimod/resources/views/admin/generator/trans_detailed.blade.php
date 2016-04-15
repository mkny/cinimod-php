@extends('cinimod::layout.admin')


@section('conteudo')
<div class="row default-admin">
	<h1 class="jumbotron">Model-Generator / Configurator - ({{ $controller or 'Default' }})</h1>
	@if (session('status'))
	<div class="alert alert-{{session('status')}}">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		{{session('message')}}
	</div>
	@endif

	<!-- <ul>
		<li><a href="javascript:;">new field</a></li>
	</ul> -->

	@include('cinimod::admin.default.form', ['form' => $form])
</div>
@stop
