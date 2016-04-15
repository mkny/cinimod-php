@extends('cinimod::layout.admin')

@section('conteudo')
<h1 class="jumbotron">Model-Generator / Configurator</h1>


<h4>Config files</h4>
<ul>
	@foreach ($configs as $config)
	<li>
		<a href="{{route('adm::config', [$config])}}">{{$config}}</a>
	</li>
	@endforeach
</ul>
@stop