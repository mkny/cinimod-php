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
    protected $signature = 'mkny:modulo {modulo} {tabela?}';

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
        $modulo = $this->argument('modulo');
        $table = $this->argument('tabela')?:str_plural($this->argument('modulo'));

        
        $this->mknyTranslate($modulo,  $table);
        $this->mknyRequest($modulo,  $table);
        $this->mknyModel($modulo,  $table);
        $this->mknyModelconfig($modulo,  $table);

        $this->mknyPresenter($modulo);
        

        $this->mknyController($this->argument('modulo'));

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
    private function mknyModel($name, $table)
    {

        $this->call("mkny:model", [
            "modelo" => $name,
            "table" => $table
            ]);
    }

    /**
     * Chama o criador de modelconfig
     * @param  string  $name  Nome do modelo
     * @param  string $table Nome da tabela
     * @return void
     */
    private function mknyModelconfig($name, $table)
    {

        $this->call("mkny:mconfig", [
            "modelconfig" => $name,
            "table" => $table
            ]);
    }
    /**
     * Chama o criador de Presenter
     * @param  string  $name  Nome do modulo
     * @return void
     */
    private function mknyPresenter($name)
    {

        $this->call("mkny:presenter", ['apresentador' => $name]);
    }

 }
