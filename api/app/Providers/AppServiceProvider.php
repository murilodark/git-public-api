<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;

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
        

        // Força HTTPS em produção para evitar erros de Mixed Content
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // 🔥 Carrega todas as subpastas de migrations automaticamente
        $migrationPaths = collect(File::directories(database_path('migrations')))
            ->push(database_path('migrations')) // inclui a raiz também
            ->toArray();

        $this->loadMigrationsFrom($migrationPaths);
    }
}
