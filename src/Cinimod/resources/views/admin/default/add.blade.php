@extends('cinimod::layout.admin')

@section('conteudo')

<div class="row default-admin">
	<h1 class="jumbotron">Add Data</h1>
	@include('cinimod::admin.default.form', ['form' => $form])
</div>

@stop
