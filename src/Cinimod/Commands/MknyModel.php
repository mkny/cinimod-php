<?php

namespace Mkny\Cinimod\Commands;

use Illuminate\Console\GeneratorCommand;

use Mkny\Cinimod\Logic;

use Schema;

class MknyModel extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkny:model {modelo} {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o arquivo de classe de extensão do Eloquent, com algumas configurações na classe';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';


    /**
     * O basepath para o arquivo
     * @var string
     */
    protected $basepath = 'Models/';

    /**
     * Informação que vai ser substituida na Stub;
     * 
     * @var array
     */
    private $translation = [];

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
        return __DIR__.'/stubs/model.stub';
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
        $table = $this->argument('table');

        // Setta a tabela
        $this->setTranslation('table', $table);

        // Setta o nome da conexao
        $this->setTranslation('connectionName', Schema::getConnection()->getName());

        // Get the table columns
        // hope this works!
        $AppLogic = new Logic\AppLogic();
        $columns = $AppLogic->buildColumnsName($table);
        
        // $columns = Schema::getColumnListing($table);

        // Verifica se a tabela tem os campos pro timestamps do laravel
        $this->setTranslation('timestamps', in_array('created_at', $columns) && in_array('updated_at', $columns) ? 1:0);

        // Setta primaryKey
        $this->setTranslation('primaryKey', array_shift($columns));

        // Setta fillable
        $this->setTranslation('fillable', "\n\t\t'".implode("',\n\t\t'", $columns)."'\n\t\t");

        // Custom functions
        $this->setTranslation('model_function_relation', $this->_getRelations($table));
        // Custom functions end
    }

    /**
     * Funcao para buscar e montar o sistema de relacionamentos do Eloquent
     * 
     * @param string $table Nome da tabela que sera gerada
     * @return string Funcao gerada
     */
    public function _getRelations($table)
    {
        $this->var_config_relations = [];
        
        $stubModelConfig = $this->files->get(__DIR__.'/stubs/model_function_relation.stub');

        // Pega os relacionamentos no banco
        $AppLogic = new Logic\AppLogic();
        $relations = $AppLogic->buildRelationships($table);
        // dd($relations);

        $field_types = $AppLogic->_getFieldTypes();

        if($relations){


            $stubs = [];
            foreach ($relations as $rel) {
                // Copia a stub mae
                $stub_child = trim(substr(trim($stubModelConfig),5))."\n\n";


                // Tentativa fim

                // Trampo pra pegar a primeira coluna para o possivel nome do campo
                $columns = $AppLogic->buildColumns($rel->table_primary,$rel->schema_primary);
                
                $field_name = $columns[0]->name;
                foreach ($columns as $column) {
                    if($field_types[$column->type] == 'string'){
                        $field_name = $column->name;
                        break;
                    }
                }

                

                // Setta as configuracoes
                $arrConfig = array(
                    'relation_class_name' => $AppLogic->controllerName($rel->table_primary),
                    'relation_field_id' => $rel->table_foreign_field,
                    'relation_field_fkey' => $rel->table_primary_field,
                    'relation_field_name' => $field_name
                    );

                // Tentativa de automatizar o processo, na criacao da var_config
                $this->var_config_relations[$rel->table_foreign_field] = $arrConfig;


                // Traduz a stub
                Logic\UtilLogic::translateStub($arrConfig, $stub_child);
                $stubs[] = $stub_child;
            }

            return implode("\n\n", $stubs);
        } else {
            return '';
        }
    }


    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return $this->basepath.ucfirst(strtolower($this->argument('modelo')));
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);//.$this->type;
        
        // return mkny_app_path().'/'.str_replace('\\', '/', $name).'.php';
        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }
}
