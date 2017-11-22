<?php

namespace Qbhy\Repository\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Qbhy\Repository\Commands\RepositoryCommand;

class AppServiceProvider extends ServiceProvider
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
    }

}
