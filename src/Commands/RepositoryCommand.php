<?php

namespace Qbhy\Repository\Commands;

use Illuminate\Console\Command;
use File;

class RepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name} {model?} {model_name?} {cache_prefix?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $class = $name . 'Repository';

        if (File::exists(app_path("Repositories/$class.php"))) {

            $this->error("$class exists !");

        } else {

            $model = $this->argument('model') ? $this->argument('model') : '\\App\\Models\\' . $name;
            $model_name = $this->argument('model_name') ? $this->argument('model_name') : $name;
            $cache_prefix = $this->argument('cache_prefix') ? $this->argument('cache_prefix') : $name . '_id:';

            $content = File::get(__DIR__ . '/../../tmp/ExampleRepository');
            $content = str_replace("{class}", $class, $content);
            $content = str_replace("{model}", $model, $content);
            $content = str_replace("{model_name}", $model_name, $content);
            $content = str_replace("{cache_prefix}", $cache_prefix, $content);

            File::put(app_path("Repositories/$class.php"), $content);

            $this->publishBaseRepository();

            $this->info('Create repository success !');
        }

    }

    public function publishBaseRepository()
    {
        $target = app_path("Repositories/Repository.php");

        if (!File::exists($target)) {

            File::copy(__DIR__ . '/../../tmp/Repository', $target);

            $this->info('Create base repository file success !');
        }
    }

}
