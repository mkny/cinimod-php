<?php

namespace Mkny\Cinimod\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MknyModulo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkny:modulo {controlador} {tabela?} {--notable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera o conjunto de ferramentas, para o objeto especificado';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     parent::__construct();
    // }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->option('notable')) {
            $this->mknyTranslate($this->argument('controlador'),  $this->argument('tabela'));
            $this->mknyRequest($this->argument('controlador'),  $this->argument('tabela'));
            $this->mknyModel($this->argument('controlador'),  $this->argument('tabela'));
            $this->mknyModelconfig($this->argument('controlador'),  $this->argument('tabela'));
            $this->mknyPresenter($this->argument('controlador'));
        }

        $this->mknyController($this->argument('controlador'));
        # CriaLogic

        echo 'finished..!';
    }
    /**
     * Chama o criador de controladores
     * @param  string $name Nome do controlador
     * @return void
     */
    private function mknyController($name)
    {
        $this->call('mkny:controller', [
            'controlador' => $name
            ]);
    }

    /**
     * Chama o criador de translate
     * @param  string $name   Nome da Translation
     * @param  string $tabela Nome da tabela
     * @return void
     */
    private function mknyTranslate($name, $tabela)
    {
        $this->call('mkny:translate', [
            'translate' => $name,
            'table' => $tabela
            ]);
    }

    /**
     * Chama o criador de formRequest
     * @param  string $name   Nome da Request
     * @param  string $tabela Nome da tabela
     * @return void
     */
    private function mknyRequest($name, $tabela)
    {
        $this->call('mkny:request', [
            'requisicao' => $name,
            'table' => $tabela
            ]);
    }

    /**
     * Chama o criador de modelo ORM-Doctrine-Eloquent
     * @param  string  $name  Nome do modelo
     * @param  string $table Nome da tabela
     * @return void
     */
    private function mknyModel($name, $table=false)
    {
        $options["modelo"] = $name;
        if ($table) {
            $options["--table"] = ($table?:'');
        }
        
        $this->call("mkny:model", $options);
    }

    /**
     * Chama o criador de modelconfig
     * @param  string  $name  Nome do modelo
     * @param  string $table Nome da tabela
     * @return void
     */
    private function mknyModelconfig($name, $table)
    {
        $options["modelconfig"] = $name;
        $options["table"] = $table;
        
        
        $this->call("mkny:mconfig", $options);
    }
    /**
     * Chama o criador de Presenter
     * @param  string  $name  Nome do modulo
     * @return void
     */
    private function mknyPresenter($name)
    {
        $options["apresentador"] = $name;
        
        
        $this->call("mkny:presenter", $options);
    }


     /**
     * Get the console command options.
     *
     * @return array
     */
    //  protected function getOptions()
    //  {
    //     return [
    //     ['--notable', null, InputOption::VALUE_OPTIONAL, 'Cancel table creation.', null]
    //     ];
    // }

 }
