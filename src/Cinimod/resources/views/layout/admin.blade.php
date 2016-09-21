<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Cinimod dominiC - Resources made ez</title>
	<!-- Styles -->
	<!-- {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}} -->
	<link rel="stylesheet" type="text/css" href="/css/app.css">
	<link rel="stylesheet" type="text/css" href="/css/cinimod.css">
	<!-- Styles -->
	<!-- Scripts -->
	<script src="/js/jquery.min.js" type="text/javascript"></script>
	<script src="/js/bootstrap.min.js" type="text/javascript"></script>

	<!-- <script src="" type="text/javascript"></script> -->
	<!-- Scripts -->
	
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
</head>
<body>
	@include('cinimod::layout.admin_navbar')
	

	<div class="container-fluid">
		@yield('conteudo')
	</div>

	@if(isset($scripts) && count($scripts) > 0)
		@foreach ($scripts as $s)
			<script type="text/javascript" src="{{$s}}"></script>
		@endforeach
	@endif

	@if(isset($scripts_static) && count($scripts_static) > 0)
		@foreach ($scripts_static as $s)
			<script type="text/javascript">{!! $s !!}</script>
		@endforeach
	@endif
</body>
</html>
