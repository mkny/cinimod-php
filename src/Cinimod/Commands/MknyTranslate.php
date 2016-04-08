<?php

namespace Mkny\Cinimod\Commands;

use Illuminate\Console\GeneratorCommand;

use Mkny\Cinimod\Logic;

class MknyTranslate extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkny:translate {translate} {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o arquivo de tradução, colocando valores default para o campo (form/grid)';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Translation';


    /**
     * O basepath para o arquivo
     * @var string
     */
    protected $basepath = '';

    /**
     * Informação que vai ser substituida na Stub;
     * 
     * @var array
     */
    private $translation = [];

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/translation.stub';
    }

    /**
     * Setter pra variavel translation
     * 
     * @param string $param Nome da variavel
     * @param string $value Valor da variavel
     */
    private function setTranslation($param, $value)
    {
        $this->translation[$param] = $value;
    }

    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        // Setta as variaveis de classe
        $this->setClassVariables();

        // Traduz a stub
        Logic\UtilLogic::translateStub($this->translation, $stub);

        // Termina a traducao da stub e retorna
        return $this
        ->replaceNamespace($stub, $name)
        
        // ->translateStub($stub)
        ->replaceClass($stub, $name)
        ;
    }

    /**
     * Replace the fillable for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function setClassVariables()
    {
        $tb = $this->getNameInput();

        // ???
        if(strstr($tb,'/') !== false){
            $tbx = explode('/',$this->getNameInput());
            $tb = $tbx[1];
        }

        // Plurazica, caso nao exista a tabela
        $table = $this->argument('table')? : str_plural(strtolower($tb));

        // Pega as colunas da tabela do banco
        $AppLogic = new Logic\AppLogic();
        $columns = $AppLogic->buildColumns($table);
        
        $fields = [];
        foreach ($columns as $col) {
            $field = $col->name;
            $field_camel = camel_case($col->name, '_', ' ');
            $fields[] = "\n\t// {$field}\n\t'{$field}_grid' => '{$field_camel}',\n\t'{$field}_form' => '{$field_camel}',\n\t'{$field}_form_tip' => '{$field_camel}',\n";
        }

        $this->setTranslation('variables_data', "\n\t".substr(trim(implode("\n", $fields)),0,-1)."\n\t");
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return (strtolower($this->argument('translate')));
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

        // return mkny_app_path().'/resources/lang/'.\App::getLocale().'/'.str_replace('\\', '/', $name).'.php';
        return base_path().'/resources/lang/'.\App::getLocale().'/'.str_replace('\\', '/', $name).'.php';
        // return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }
}
