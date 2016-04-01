@extends('cinimod::layout.admin')

@section('conteudo')

<div class="row">
	<div class="col-md-6">a</div>
	<div class="col-md-6">b</div>
</div>

<div class="row">
	<div class="col-md-6">
		<!-- <div data-chart="report_geo_selling" data-charttype="geo"></div> -->
		<div class="item"></div>
	</div>
	<div class="col-md-6">
		<!-- <div data-chart="report_questions_by_category" data-charttype="pie"></div> -->
		<div id="chart"></div>
		
	</div>
</div>
@stop