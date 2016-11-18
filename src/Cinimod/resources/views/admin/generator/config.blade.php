@extends('cinimod::layout.admin')

@section('conteudo')
<div class="row">
	<div class="col-md-12">
		<h1 class="jumbotron">Model-Generator / Configurator</h1>
	</div>
</div>
<div class="row-fluid">
	<div class="col-md-12">
		<h4>Config files</h4>
		<ul class="">
			@foreach ($configs as $config)
			<li>
				<a class="" href="{{action('\\'.$controller.'@getFile', [$config])}}">{{$config}}</a>
			</li>
			@endforeach
		</ul>
	</div>
</div>
@stop