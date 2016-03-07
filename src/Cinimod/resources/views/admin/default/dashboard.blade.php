@extends('layout.admin')

@section('conteudo')
<h1 class="jumbotron">Hello</h1>



<div class="row">
	@foreach ( $reports as $reportData )
	@foreach ( $reportData as $reportName => $reportType )
	<div class="col-md-6">
		<div data-chart="{{$reportName}}" data-charttype="{{$reportType}}"></div>
	</div>
	@endforeach
	@endforeach
</div>
<div class="row">
	<div class="col-md-12">
		
	</div>
</div>

@stop