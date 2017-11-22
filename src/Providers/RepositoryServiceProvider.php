<?php

namespace Qbhy\Repository\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Qbhy\Repository\Commands\RepositoryCommand;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->commands([
            RepositoryCommand::class,
        ]);

        $this->bindRepositories();

    }

    public function bindRepositories(): void
    {
        $repositories = [];
        $dir_name = app_path('Repositories');

        foreach (glob($dir_name . '/*Repository.php') as $filename) {
            $name = str_replace($dir_name . '/', "", $filename);
            $repositories[] = 'App\\Repositories\\' . str_replace(".php", "", $name);
        }

        foreach ($repositories as $repository) {
            $this->app->singleton($repository, function ($app) use ($repository) {
                return new $repository();
            });
        }
    }

}
