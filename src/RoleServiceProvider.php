<?php

namespace Moawiaab\Role;

use Illuminate\Support\ServiceProvider;

class RoleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'role-migrations');

        $this->publishes([
            __DIR__ . '/../database/seeders/' => database_path('seeders')
        ], 'role-migrations');

        $this->publishes([
            __DIR__ . '/Models' => app_path('Models')
        ], 'role-migrations');

        $this->publishes([
            __DIR__ . '/Http/Middleware/' => app_path('Http/Middleware')
        ], 'role-migrations');
    }
}
