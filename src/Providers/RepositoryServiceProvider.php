<?php

namespace Qbhy\Repository\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Qbhy\Repository\Commands\RepositoryCommand;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

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

        $this->setupConfig();

        if (config('repository.auto_bind', true)) {
            $this->bindRepositories();
        }

    }

    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $configSource = realpath(__DIR__ . '/../config.php');
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([
                $configSource => config_path('repository.php')
            ]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('repository');
        }
        $this->mergeConfigFrom($configSource, 'repository');

        $this->commands([
            RepositoryCommand::class,
        ]);
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
