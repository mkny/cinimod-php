<?php

namespace Mkny\Cinimod\Commands;

use Illuminate\Console\GeneratorCommand;

use Mkny\Cinimod\Logic;

use Schema;

class MknyRequest extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkny:request {requisicao} {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o arquivo de validação, obedecendo critérios pre-estabelecidos e vindos do database.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

    /**
     * O basepath para o arquivo
     * @var string
     */
    // protected $basepath = 'Requests/';
    protected $basepath = 'Http/Requests/';

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
        return __DIR__.'/stubs/request.stub';
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
        ->replaceClass($stub, $name);
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
        
        if(strstr($tb,'/') !== false){
            $tbx = explode('/',$this->getNameInput());
            $tb = $tbx[1];
        }

        $table = $this->argument('table')? : str_plural(strtolower($tb));

        // Pega as colunas da tabela do banco
        $AppLogic = new Logic\AppLogic();
        $columns = $AppLogic->buildColumnsWithKeys($table);
        // echo '<pre>';
        // print_r($columns);
        // exit;
        // Pega os field_types (translation)
        $field_types = $AppLogic->_getFieldTypes();

        $strRules = '';
        foreach ($columns as $key => $col) {
            // Debug Estado!
            // echo '<pre>';
            // print_r($col);
            // exit;
            // Pula a primary key
            if($key==0) {
                continue;
            }
            // Armazena as regras para o campo
            $arrRule = [];


            // Check constraint

            if (isset($col->def_constraint) && !empty($col->def_constraint)) {
                // var_dump($col->def_constraint);exit;
                $check = $AppLogic->_getCheck($col->def_constraint);
                $col->type = 'char';
                $arrRule[] = 'in:'.implode(',',  $check);
            }

            // Unique field
            if (isset($col->uniq_field) && $col->uniq_field) {
                $arrRule[] = 'unique:'.$col->uniq_field;
            }

            // Informa o tipo de dado
            $col->field_type = $field_types[$col->type];

            // Verifica o tamanho maximo do campo
            if ($col->length) {
                $arrRule[] = "max:{$col->length}";
            }

            // Verifica se o campo nao aceita nulo e se e date com valor default
            if($col->is_null == 'NO'){
                if ($col->field_type == 'date' && trim($col->default_value) != '') {

                } else {
                    $arrRule[] = 'required';
                }
            }
            // echo '<pre>';
            // print_r($arrRule);
            // exit;
            // Adiciona o tipo na validacao
            $arrRule[] = $col->field_type;

            // Inverte pra ficar bonito :)
            $arrRule = array_reverse($arrRule);

            // Monta pro Request, o formato de validacao
            $strRules .= "\n\t\t'{$col->name}' => '".implode('|', $arrRule)."',";
        }

        // Setta validation_block
        $this->setTranslation('validation_block', substr($strRules,0,-1)."\n\t\t");
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return $this->basepath.ucfirst(strtolower($this->argument('requisicao')));
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

        // return mkny_app_path().'/'.str_replace('\\', '/', $name).'.php';
        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }
}
