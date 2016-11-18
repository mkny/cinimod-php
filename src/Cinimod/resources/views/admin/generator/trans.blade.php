@extends('cinimod::layout.admin')

@section('conteudo')
<div class="row">
	<div class="col-md-12">
		<h1 class="jumbotron">Model-Generator / Configurator</h1>
	</div>
</div>

<div class="row-fluid">
	<div class="col-md-12">
		<h4>Languages</h4>
		<ul>
			@foreach ($langlist as $lang)
			<li><a href="{{route('adm::trans', ['lang' => $lang])}}">{{$lang}}</a></li>
			@endforeach
		</ul>
	</div>
</div>
<div class="row-fluid">
	<div class="col-md-12">
		<h4>Language files</h4>
		<ul>
			@foreach ($langfiles as $langf)
			<li>
				<a href="{{action('\\'.$controller.'@getFile', [$langlist_sel, $langf])}}">{{$langf}}</a>
			</li>
			@endforeach
		</ul>
	</div>
</div>
@stop