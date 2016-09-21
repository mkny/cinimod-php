var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

 elixir(function(mix) {
 	mix
    // bootstrap related
    .copy('node_modules/bootstrap-sass/assets/fonts', 'public/fonts')
    .copy('node_modules/bootstrap-sass/assets/images', 'public/images')
    .copy('node_modules/bootstrap-sass/assets/javascripts', 'public/js')

 	// test data
 	// .copy('node_modules/tablesorter/dist/js/jquery.tablesorter.min.js', 'public/javascripts')
 	// .copy('node_modules/datatables.net/js/jquery.dataTables.js', 'public/javascripts')
 	// .copy('node_modules/datatables.net-bs/js/dataTables.bootstrap.js', 'public/javascripts')
 	
 	// jquery related
 	.copy('node_modules/jquery/dist/jquery.min.js', 'public/js/')

 	// cinimod stuff
 	.scriptsIn('resources/assets/cinimod/javascript', 'public/js/cinimod.js')
 	.stylesIn('resources/assets/cinimod/css', 'public/css/cinimod.css')
 	.copy('resources/assets/cinimod/favicon.ico', 'public/favicon.ico')
 	.copy('resources/assets/cinimod/img', 'public/img/');
 });

 // "Mkny\\": "/Users/marcony.neves/Documents/GitHub/cinimod/src"