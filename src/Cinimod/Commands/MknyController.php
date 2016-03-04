<?php

namespace Mkny\Cinimod\Commands;

use Illuminate\Console\GeneratorCommand;
// use Symfony\Component\Console\Input\InputOption;

use Mkny\Cinimod\Logic;

use Illuminate\Support\Str;

class MknyController extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkny:controller {controlador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera controlador pre-definido para interação com o CRUDController';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * O basepath para o arquivo
     * @var string
     */
    protected $basepath = 'Controllers/';
    // protected $basepath = 'Http/Controllers/';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/controller.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {

        return $this->basepath.ucfirst(strtolower($this->argument('controlador')));
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name).$this->type;
        
        return mkny_app_path().'/'.str_replace('\\', '/', $name).'.php';
        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }
}
