<?php

namespace App\Providers;

use App\Models\Programacion;
use App\Policies\ProgramacionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Programacion::class => ProgramacionPolicy::class,
        // Aquí puedes registrar otras policies si las creas
        // OtroModelo::class => OtraPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Registra las policies definidas en $policies
        $this->registerPolicies();

        // Super admin tiene permiso para todo
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
