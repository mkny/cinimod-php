@extends('layout.admin')



@section('conteudo')
<div class="row default-admin">
	<h1 class="jumbotron">Edit Data</h1>
	@if (session('status'))
	<div class="alert alert-{{session('status')}}">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		{{session('message')}}
	</div>
	@endif
</div>
@include('admin.default.form')
@stop
