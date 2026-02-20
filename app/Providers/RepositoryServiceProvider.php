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

use App\Repositories\Notification\NotificationInterface;
use App\Repositories\Notification\NotificationRepository;

use App\Repositories\ApprovalInbox\ApprovalInboxInterface;
use App\Repositories\ApprovalInbox\ApprovalInboxRepository;

use App\Repositories\Approval\ApprovalInterface;
use App\Repositories\Approval\ApprovalRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CreacionInterface::class, CreacionRepository::class);
        $this->app->bind(CatalogoInterface::class, CatalogoRepository::class);
        $this->app->bind(FechaInterface::class, FechaRepository::class);
        $this->app->bind(SalarioInterface::class, SalarioRepository::class);
        $this->app->bind(ProgramacionInterface::class, ProgramacionRepository::class);
        $this->app->bind(NotificationInterface::class, NotificationRepository::class);
        $this->app->bind(ApprovalInboxInterface::class, ApprovalInboxRepository::class);
        $this->app->bind(ApprovalInterface::class, ApprovalRepository::class);
    }
}
