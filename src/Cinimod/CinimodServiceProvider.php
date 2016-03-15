<?php

namespace Mkny\Cinimod;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

use Mkny\Cinimod\Commands;

use Mkny\Cinimod\Logic;

use Config;
use Illuminate\Foundation\AliasLoader;
use Collective;
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
    	// $this->loadViewsFrom(mkny_path().'/Cinimod/resources/views');

		$this->publishes([
			// mkny_path().'/Cinimod/resources/views' => resource_path('views/cinimod'),
			// mkny_path().'/Cinimod/resources/lang' => resource_path('lang'),
			mkny_path().'/Cinimod/resources/assets' => resource_path('assets/cinimod')
			]);
		// __DIR__.'/path/to/config/courier.php' => config_path('courier.php'),

		// $this->loadTranslationsFrom(mkny_app_path().'/resources/lang', 'cinimod');

		// AliasLoader::getInstance()->alias('FormField', 'Way\Form\FormField');
		// $loader = \Illuminate\Foundation\AliasLoader::getInstance();
  //     	$loader->alias('Tools', 'Pulpitum\Core\Models\Tools');
	}
	
	public function register() {
		// app('')
		// $this->app->bind('Class');
		$this->app->register(Collective\Html\HtmlServiceProvider::class);
		AliasLoader::getInstance()->alias('Form', Collective\Html\FormFacade::class);
		AliasLoader::getInstance()->alias('Html', Collective\Html\HtmlFacade::class);

	}

	private function artisanize(){
		$this->commands([
        // Commands\Inspire::class,
			Commands\MknyController::class,
			Commands\MknyDeleter::class,
			Commands\MknyModel::class,
			Commands\MknyModelconfig::class,
			Commands\MknyModulo::class,
			Commands\MknyPresenter::class,
			Commands\MknyRequest::class,
			Commands\MknyTranslate::class,
			]);
	}

	public function routerize(Router $router)
	{
		// Route for the admin controllers
		$router->middleware('admin','Mkny\Cinimod\Middleware\Admin');
		$router->middleware('site','Mkny\Cinimod\Middleware\Site');

		$router->group([
			'middleware' => ['web', 'admin'],
			'prefix' => 'admin',
			'as' => 'adm::',
			'namespace' => $this->namespace
			], function($router){
				
				$router->get('/', function(){
					return redirect()->route('adm::gen::index');
				});
				$router->controller('g', 'GeneratorController', [
					'getIndex' => 'gen::index',
					'getDeleter' => 'gen::del',
					'getConfig' => 'config'
					]);
				$router->controller('dashboard', 'DashboardController');
				$router->controller('rel', 'ReportController');
			});

		$this->routeResolver($router);

		

	}

	public function routeResolver($router)
	{
		// Router resolver
		$fs = new \Illuminate\Filesystem\Filesystem();
		$controllersPath = mkny_app_path().'/Controllers';
		$files = $fs->files($controllersPath);
		if(!count($files)){
			return;
		}

		$router->group([
			'namespace' => 'Mkny\App\Controllers',
			'as' => 'app',
			'prefix' => 'resolver',
			], function($router) use($files) {
				foreach ($files as $c) {
					$router->controller(strtolower(substr(basename($c),0,1)),substr(basename($c),0,-4));
				}
			});

		
	}
}