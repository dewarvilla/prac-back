<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Creacion\CreacionInterface;
use App\Repositories\Creacion\CreacionRepository;

use App\Repositories\Catalogo\CatalogoInterface;
use App\Repositories\Catalogo\CatalogoRepository;

use App\Repositories\Fecha\FechaInterface;
use App\Repositories\Fecha\FechaRepository;

use App\Repositories\Salario\SalarioInterface;
use App\Repositories\Salario\SalarioRepository;

use App\Repositories\Programacion\ProgramacionInterface;
use App\Repositories\Programacion\ProgramacionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CreacionInterface::class, CreacionRepository::class);
        $this->app->bind(CatalogoInterface::class, CatalogoRepository::class);
        $this->app->bind(FechaInterface::class, FechaRepository::class);
        $this->app->bind(SalarioInterface::class, SalarioRepository::class);
        $this->app->bind(ProgramacionInterface::class, ProgramacionRepository::class);
    }
}
