@extends('cinimod::layout.admin')

@section('conteudo')
<div class="row-fluid default-admin admin-list">
	<div class="col-md-10 col-md-offset-1">
		<div class="row">
			<h1 class="jumbotron">{{ trans($controller.'.title_list') }}<p>{{ trans($controller.'.title_list_subtitle') }}</p></h1>
		</div>
		<!-- <div class="row">
			<ol class="breadcrumb">
				<li><a href="{{ route('adm::index') }}">Home</a></li>
				<li><a href="{{ action('Admin\\'.$controller.'Controller@getIndex') }}">{{ $controller }}</a></li>
				<li>Listagem</li>
			</ol>
		</div> -->
		
		<div class="row">
			<div class="col-md-12">
				teste
			</div>
		</div>
		@if (session('status'))
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-{{session('status')}}">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					{{session('message')}}
				</div>
			</div>
		</div>
		@endif

		<div class="row">
			<div class="col-md-12">
				@if (isset($configuration['add']) && $configuration['add'])
				<a title="{{ trans($controller.'.button_new') }}" href="{{ action('Admin\\'.$controller.'Controller@getAdd') }}">
					<button type="button" class="btn btn-primary">
						<span class="glyphicon glyphicon-file"></span> {{trans($controller.'.button_new')}}
					</button>
				</a>
				@endif
			</div>
		</div>
		<form action="?" method="get">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="table-responsive">
							{{ $table }}
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="row">
								<div class="col-md-2">
									<label>Search:</label>
								</div>
								<div class="col-md-5">
									<input type="text" class="form-control admin-list-search" value="{!! isset(\Request::input('filter')['global']) ? \Request::input('filter')['global']:'' !!}" name="filter[global]" />
								</div>
								<div class="col-md-5">
									<button role="submit" class="btn btn-success" type="submit">Buscar</button>
								</div>
							</div>
						</div>
						<div class="col-md-1">Total ({{ $info['total'] }})</div>
						<div class="col-md-2">
							<div class="row">
								<div class="col-md-2">
									<small>
										<label for="">#</label>
									</small>
								</div>
								<div class="col-md-10">
									<select name="perpage" class="form-control" onchange="this.form.submit();" data-value="{!! \Request::input('perpage') !!}">
										<option value="">10</option>
										<option value="20">20</option>
										<option value="50">50</option>
										<option value="100">100</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-5">
							<!-- <div class="col-md-5 col-md-offset-4"> -->
							<div class="text-right">
								<!-- pagination pagination-sm -->
								{{ $info['links'] }}
								
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
@stop
