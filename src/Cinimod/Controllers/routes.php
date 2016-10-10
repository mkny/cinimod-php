<?php



// Sai do csrf
// Route::group([
// 	// 'middleware' => ['web', 'admin'],
// 	'prefix' => 'admin',
// 	'as' => 'adm::',
// 	// 'namespace' => $this->namespace
// 	], function($router){
// 	Route::controller('dashboard', 'DashboardController');
// 	Route::controller('rel', 'ReportController');
// });



Route::group([
	'middleware' => ['web', 'admin'],
	'prefix' => 'admin',
	'as' => 'adm::',
	// 'namespace' => $this->namespace
], function($router){
	// Route::get('/', function(){
	// 	return redirect()->route('adm::gen::index');
	// });
	Route::any('/', 'DashboardController@anyIndex');
	Route::controller('g', 'GeneratorController', [
		'getIndex' => 'gen::index',
		'getDeleter' => 'gen::del',
		'getConfig' => 'config',
		'getTrans' => 'trans'
	]);
	// Route::controller('dashboard', 'DashboardController');
});
