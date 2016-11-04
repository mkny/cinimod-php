<?php

namespace Mkny\Cinimod\Console;

use Illuminate\Console\GeneratorCommand;

use Schema;

class MknyPresenter extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkny:presenter {apresentador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o [presenter]';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Presenter';


    /**
     * O basepath para o arquivo
     * @var string
     */
    protected $basepath = 'Presenters/';

    /**
     * Informacao para auxiliar na criacao da var_config, informando as relacoes
     * 
     * @var array
     */
    private $var_config_relations = [];

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/presenter.stub';
    }


    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return $this->basepath.ucfirst(strtolower($this->argument('apresentador'))).$this->type;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);

        // return mkny_app_path().'/'.str_replace('\\', '/', $name).'.php';
        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }
}
