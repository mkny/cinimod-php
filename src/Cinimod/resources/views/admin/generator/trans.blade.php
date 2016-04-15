@extends('cinimod::layout.admin')

@section('conteudo')
<h1 class="jumbotron">Model-Generator / Configurator</h1>


<h4>Languages</h4>
<ul>
	@foreach ($langlist as $lang)
	<li><a href="{{route('adm::trans', [$lang])}}">{{$lang}}</a></li>
	@endforeach
</ul>


<h4>Language files</h4>
<ul>
	@foreach ($langfiles as $langf)
	<li>
		<a href="{{route('adm::trans', [$langlist_sel, $langf])}}">{{$langf}}</a>
	</li>
	@endforeach
</ul>

@stop