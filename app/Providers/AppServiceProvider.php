<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Catalogo\CatalogoInterface;
use App\Repositories\Catalogo\CatalogoRepository;
use App\Services\CatalogoService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ...

        $this->app->bind(CatalogoInterface::class, CatalogoRepository::class);

        $this->app->bind(CatalogoService::class, function ($app) {
            return new CatalogoService(
                $app->make(CatalogoInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
