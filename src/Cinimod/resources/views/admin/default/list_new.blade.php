@extends('cinimod::layout.admin')

@section('conteudo')
<div class="row default-admin">
	<div class="col-md-12">
		<div class="row">
			<h1 class="jumbotron">{{$data['title'] or 'List'}}</h1>
		</div>
		<!-- <div class="row">
			<ol class="breadcrumb">
				<li><a href="#">Home</a></li>
				<li><a href="#">Library</a></li>
				<li>Data</li>
			</ol>
		</div> -->
		
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
				<a title="{{ trans($data['controller'].'.button_new') }}" href="{{action($data['controller'].'Controller@getAdd')}}">
					<button type="button" class="btn btn-primary">
						<span class="glyphicon glyphicon-file"></span> {{trans($data['controller'].'.button_new')}}
					</button>

				</a>
			</div>
		</div>
		<form action="?" method="get">
			<div class="row">
				<div class="table-responsive">
					{{ $data['table'] }}
				</div>
				<div class="row-fluid">
					<div class="col-md-2">Total ({{$data['grid']->total()}})</div>
					<!-- <div class="col-md-2">
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
					</div> -->
					<div class="col-md-8">
						<div class="text-right">
							{{ $data['grid']->links() }}
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
@stop
