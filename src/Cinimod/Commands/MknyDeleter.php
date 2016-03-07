<?php

namespace Mkny\Cinimod\Commands;


use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MknyDeleter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mkny:deleter {controller} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleta um [Modulo] com todos as suas dependencias;';

    /**
     * Filesystem
     * @var object
     */
    private $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $f)
    {
        parent::__construct();
        $this->files = $f;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $c = strtolower($this->argument('controller'));
        // exit($c);

        $files = [];

        $files[] = mkny_models_path($c).'.php';
        $files[] = mkny_model_config_path($c).'.php';
        $files[] = mkny_presenters_path($c).'Presenter.php';
        $files[] = mkny_controllers_path($c).'Controller.php';
        $files[] = mkny_requests_path($c).'Request.php';
        $files[] = mkny_lang_path(\App::getLocale().'/'.$c).'.php';

        $errors = [];
        
        foreach ($files as $file) {
            if(!$this->files->exists($file)){
                $errors[] = $file;
            }
        }

        if (!$this->option('force') && count($errors)) {
            $this->error("Nao foi possivel executar o delete-automatico!\nAlguns arquivos estao ausentes!");
        } else {
            if($this->option('force') || $this->confirm("Deseja realmente remover os arquivos: \n'".implode("',\n'", $files))){
                foreach ($files as $file) {
                    $this->files->delete($file);
                }

                $this->info('Deleted!');
            }
        }




    }
}
