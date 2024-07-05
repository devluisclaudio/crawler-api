<?php

namespace App\Providers;

use App\Interfaces\BuscaPrecoRepositoryInterface;
use App\Repositories\BuscaPrecoRepository;
use App\Repositories\MenorPrecoRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            BuscaPrecoRepositoryInterface::class,
            BuscaPrecoRepository::class,
            MenorPrecoRepository::class,
        );

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
