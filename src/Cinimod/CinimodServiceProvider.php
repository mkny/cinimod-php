<?php

namespace Mkny\Cinimod;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

use Mkny\Cinimod\Console;

use Mkny\Cinimod\Logic;

use Illuminate\Foundation\AliasLoader;

use Config;
use Blade;
// use Collective;
/**
* 
*/
class CinimodServiceProvider extends ServiceProvider
{
	protected $namespace = 'Mkny\Cinimod\Controllers';

	public function boot(Router $router)
	{
		parent::boot($router);
		$this->artisanize();
		$this->routerize($router);

    	$this->loadViewsFrom(mkny_path().'/Cinimod/resources/views', 'cinimod');
    	// $this->loadViewsFrom(resource_path('themes'), 'unicorn');
    	// $this->loadViewsFrom(mkny_path().'/Cinimod/resources/views');

		$this->publishes([
			// publish images
			// mkny_path().'/Cinimod/resources/views' => resource_path('views/cinimod'),
			// mkny_path().'/Cinimod/resources/lang' => resource_path('lang'),
			// mkny_path().'/Cinimod/resources/assets' => resource_path('assets/cinimod')
			mkny_path().'/Cinimod/resources/assets' => resource_path('assets/cinimod')
			]);
		// __DIR__.'/path/to/config/courier.php' => config_path('courier.php'),

		// $this->loadTranslationsFrom(mkny_app_path().'/resources/lang', 'cinimod');

		// AliasLoader::getInstance()->alias('FormField', 'Way\Form\FormField');
		// $loader = \Illuminate\Foundation\AliasLoader::getInstance();
  //     	$loader->alias('Tools', 'Pulpitum\Core\Models\Tools');
      	// que locura ta isso ein! hahaha
      	// 


	}
	
	public function register() {
		$this->app->singleton('front', function ($app) {
            return new \Mkny\Front\FrontLayer();
        });

		$this->app->register(\Lavary\Menu\ServiceProvider::class);
		$this->app->register(\Collective\Html\HtmlServiceProvider::class);


        // $this->app->alias('html', 'Collective\Html\HtmlBuilder');
        // $this->app->alias('form', 'Collective\Html\FormBuilder');
		AliasLoader::getInstance()->alias('Front', \Mkny\Front\FrontFacade::class);
		AliasLoader::getInstance()->alias('Form', \Collective\Html\HtmlServiceProvider::class);
		AliasLoader::getInstance()->alias('Form', \Collective\Html\FormFacade::class);
		AliasLoader::getInstance()->alias('Html', \Collective\Html\HtmlFacade::class);
		AliasLoader::getInstance()->alias('Menu', \Lavary\Menu\Facade::class);
	}

	private function artisanize(){
		$this->commands([
        // Commands\Inspire::class,
			Console\MknyController::class,
			Console\MknyDeleter::class,
			Console\MknyModel::class,
			Console\MknyModelconfig::class,
			Console\MknyModulo::class,
			Console\MknyPresenter::class,
			Console\MknyRequest::class,
			Console\MknyTranslate::class,
			]);
	}

	public function routerize(Router $router)
	{
		// Route for the admin controllers
		$router->middleware('admin','Mkny\Cinimod\Middleware\Admin');
		$router->middleware('site','Mkny\Cinimod\Middleware\Site');

		

		$router->group([
            'namespace' => $this->namespace,
            'middleware' => 'web',
        ], function ($router) {
            require mkny_path('\Cinimod\Controllers\routes.php');
            // require mkny_path('\Cinimod\Controllers\routes.php');
        });
		
		

		// $this->routeResolver($router);

		

	}

	// public function routeResolver($router)
	// {
	// 	// Router resolver
	// 	$fs = new \Illuminate\Filesystem\Filesystem();
	// 	$controllersPath = mkny_app_path().'/Controllers';
	// 	$files = $fs->files($controllersPath);
	// 	if(!count($files)){
	// 		return;
	// 	}

	// 	$router->group([
	// 		'namespace' => 'Mkny\App\Controllers',
	// 		'as' => 'app',
	// 		'prefix' => 'resolver',
	// 		], function($router) use($files) {
	// 			foreach ($files as $c) {
	// 				$router->controller(strtolower(substr(basename($c),0,1)),substr(basename($c),0,-4));
	// 			}
	// 		});

		
	// }
}