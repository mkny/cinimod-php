<?php

namespace Mkny\Cinimod\Console;

use Illuminate\Console\GeneratorCommand;

use Mkny\Cinimod\Logic;

// var_export do the job kkkkk
class MknyModelconfig extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkny:mconfig {modelconfig} {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o arquivo de configuração do [Model]';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model config';


    /**
     * O basepath para o arquivo
     * @var string
     */
    protected $basepath = 'Modelconfig/';

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
        return __DIR__.'/stubs/model_config.stub';
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
        $tb = $this->getNameInput();

        // ???
        if(strstr($tb,'/') !== false){
            $tbx = explode('/',$this->getNameInput());
            $tb = $tbx[1];
        }

        // Plurazica, caso nao exista a tabela
        $table = $this->argument('table')? : str_plural(strtolower($tb));
        $this->_getRelations($table);

        // model_config.stub;
        $stub = $this->files->get(__DIR__.'/stubs/model_config.stub');
        
        $this->setTranslation('var_fields_data', $this->_getModelConfig($table));

        Logic\UtilLogic::translateStub($this->translation, $stub);
        
        return $stub;
        // return "<?php\n\n\nreturn [\n".$this->_getModelConfig($table)."\n];";
    }



    /**
     * Funcao para buscar e montar o sistema de relacionamentos do Eloquent
     * 
     * @param string $table Nome da tabela que sera gerada
     * @return void
     */
    private function _getRelations($table)
    {
        $this->var_config_relations = [];

        // Pega os relacionamentos no banco
        $AppLogic = new Logic\AppLogic();
        $relations = $AppLogic->buildRelationships($table);
        // dd($relations);

        $field_types = $AppLogic->_getFieldTypes();

        if($relations){


            $stubs = [];
            foreach ($relations as $rel) {
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
            }
        }

    }

    /**
     * Monta o template do configurador de model (var)
     * 
     * @return  array Dados do config
     */
    private function _getModelConfig($table){
        $stubModelConfig = $this->files->get(__DIR__.'/stubs/model_config_fields.stub');

        // Pega as colunas da tabela do banco
        $AppLogic = new Logic\AppLogic();
        $columns = $AppLogic->buildColumnsWithKeys($table);

        $field_types = $AppLogic->_getFieldTypes();

        $stubs = [];
        foreach ($columns as $indice => $row) {
            // Copia a stub mae
            $stub_child = trim(substr(trim($stubModelConfig),5))."\n\n";

            // Setta as configuracoes
            $arrConfig = array(
                'var_name' => $row->name,
                'var_type' => $indice === 0 ? 'primaryKey':$row->type,
                'var_type_laravel' => $indice === 0 ? 'primaryKey':$field_types[$row->type],
                'go_to_grid' => 'true',
                'go_to_form_add' => $indice === 0 ? 'false':'true',
                'go_to_form_edit' => $indice === 0 ? 'false':'true',
                'is_required' => $row->is_null == 'NO' ? 'true':'false',
                'var_relationship' => 'false',
                'var_values' => 'false',
                'num_ordem' => $indice,
                );

            // Trampa nos values pre-definidos

            if (isset($row->def_constraint) && !empty($row->def_constraint)) {
                // var_dump($col->def_constraint);exit;
                $check = $AppLogic->_getCheck($row->def_constraint);
                $arrConfig['var_type_laravel'] = 'select';
                $arrConfig['var_values'] = "['".implode("','",$check)."']";
            }

            // Trampa na relacao
            if(isset($this->var_config_relations[$row->name])){
                $arrConfig['var_type_laravel'] = 'select';

                // variabilizar o relationship
                $arrConfig['var_relationship'] = 
                "\n\t[\n\t\t".
                "'model' => '\App\Models\\".$this->var_config_relations[$row->name]['relation_class_name']."',\n\t\t".
                    // "'model' => '\App\Models\{$this->var_config_relations[$row->name]['relation_class_name']}',\n\t\t".
                "'field_key' => '{$this->var_config_relations[$row->name]['relation_field_id']}',\n\t\t".
                "'field_fkey' => '{$this->var_config_relations[$row->name]['relation_field_fkey']}',\n\t\t".
                "'field_show' => '{$this->var_config_relations[$row->name]['relation_field_name']}',\n\t\t".
                "'where' => false,\n\t\t".
                "]\n\t";

            }
            // Trampa fim

            // Traduz a stub
            Logic\UtilLogic::translateStub($arrConfig, $stub_child);
            $stubs[] = $stub_child;
        }

        return implode("\n\n", $stubs);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return $this->basepath.ucfirst(strtolower($this->argument('modelconfig')));
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
