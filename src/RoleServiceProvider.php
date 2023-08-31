<?php

namespace Moawiaab\Role;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Contracts\Http\Kernel;


class RoleServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/my_role.php', 'role');

        $this->callAfterResolving(BladeCompiler::class, function () {
            if (config('role.stack') === 'api') {

            }
        });
    }
    
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->configureCommands();

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'role-migrations');

        $this->publishes([
            __DIR__ . '/../database/seeders/' => database_path('seeders')
        ], 'role-migrations');

        copy(__DIR__.'/Models/User.php', app_path('Models/User.php'));
        
        // if (config('role.stack') === 'inertia') {
        // }
        $this->bootInertia();

        // $this->publishes([
        //     __DIR__ . '/Models' => app_path('Models')
        // ], 'role-migrations');

        // $this->publishes([
        //     __DIR__ . '/Http/Middleware/' => app_path('Http/Middleware')
        // ], 'role-migrations');
    }

        /**
     * Configure the commands offered by the application.
     *
     * @return void
     */
    protected function configureCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
        ]);
    }

        /**
     * Boot any Inertia related services.
     *
     * @return void
     */
    protected function bootInertia()
    {
        $kernel = $this->app->make(Kernel::class);

        $kernel->appendMiddlewareToGroup('web', ShareInertiaData::class);
        $kernel->appendToMiddlewarePriority(ShareInertiaData::class);

        if (class_exists(HandleInertiaRequests::class)) {
            $kernel->appendToMiddlewarePriority(HandleInertiaRequests::class);
        }

    }

}
