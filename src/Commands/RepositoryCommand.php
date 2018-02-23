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
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $filename = $name . 'Repository';

        $dirs = explode('/', $name);

        $name = array_pop($dirs);

        $class = $name . 'Repository';

        $has_dir = $this->createDirs($dirs);

        if (File::exists(base_path("app/Repositories/$filename.php"))) {

            $this->error("$filename exists !");

        } else {

            $model = $this->argument('model') ? $this->argument('model') : '\\App\\Models\\' . $name;
            $model_name = $this->argument('model_name') ? $this->argument('model_name') : $name;
            $cache_prefix = $this->argument('cache_prefix') ? $this->argument('cache_prefix') : $name . '_id:';

            $content = File::get(__DIR__ . '/../../tmp/ExampleRepository');

            if ($has_dir) {
                $content = str_replace("{namespace}", "\\" . implode("\\", $dirs), $content);
            } else {
                $content = str_replace("{namespace}", "", $content);
            }

            if ($has_dir) {
                $content = str_replace("{use}", PHP_EOL . "use App\\Repositories\\Repository;" . PHP_EOL, $content);
            } else {
                $content = str_replace("{use}", "", $content);
            }

            $content = str_replace("{class}", $class, $content);
            $content = str_replace("{model}", $model, $content);
            $content = str_replace("{model_name}", $model_name, $content);
            $content = str_replace("{cache_prefix}", $cache_prefix, $content);

            File::put(base_path("app/Repositories/$filename.php"), $content);

            $this->publishBaseRepository();

            $this->info('Create repository success !');
        }

    }

    public function createDirs(array $dirs): bool
    {
        if ($dirs) {
            $dir = implode('/', $dirs);
            $dir = base_path("app/Repositories/$dir");

            if (count($dirs) > 0 && !File::exists($dir)) {
                File::makeDirectory($dir);
            }
            return true;
        }

        return false;
    }

    public function publishBaseRepository(): void
    {
        $target = base_path("app/Repositories/Repository.php");

        if (!File::exists($target)) {

            File::copy(__DIR__ . '/../../tmp/Repository', $target);

            $this->info('Create base repository file success !');
        }
    }

}
