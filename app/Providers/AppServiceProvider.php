<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // register role/permission middleware aliases provided by spatie package
        $router = $this->app['router'];
        // package places these middleware under \Spatie\Permission\Middleware (no "s" in folder name)
        $router->aliasMiddleware('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
        $router->aliasMiddleware('permission', \Spatie\Permission\Middleware\PermissionMiddleware::class);
        $router->aliasMiddleware('role_or_permission', \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class);

        // custom middleware that bundles authentication + admin check
        $router->aliasMiddleware('admin.only', \App\Http\Middleware\AdminOnly::class);
    }
}
