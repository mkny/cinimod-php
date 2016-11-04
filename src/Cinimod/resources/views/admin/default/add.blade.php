@extends('cinimod::layout.admin')
@section('conteudo')

<div class="row-fluid default-admin">
	<div class="col-md-10 col-md-offset-1">
		<div class="row">
			<div class="col-md-12">
				<h1 class="jumbotron">{{trans($controller.'.title_add')}}<p>{{ trans($controller.'.title_add_subtitle') }}</p></h1>
			</div>
		</div>
		<!-- <div class="row">
			<div class="col-md-12">
				<ol class="breadcrumb">
					<li><a href="{{ route('adm::index') }}">Home</a></li>
					<li><a href="{{ action('Admin\\'.$controller.'Controller@getIndex') }}">{{ $controller }}</a></li>
					<li>Add</li>
				</ol>
			</div>
		</div> -->
		@include('cinimod::admin.default.form', ['form' => $form])
	</div>
</div>

@stop
