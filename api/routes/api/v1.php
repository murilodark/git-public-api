<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

$version = 'v1';
$domains = ['admin', 'cliente', 'parceiro', 'common'];

/**
 * 🔓 PUBLIC
 * (sem prefixo "public")
 * Correção: Removido 'check.manutencao' do array de middlewares
 */
Route::prefix("api/{$version}")
    ->middleware(['api'])
    ->group(function () use ($version) {

        $path = base_path("routes/api/{$version}/public");

        if (is_dir($path)) {
            foreach (File::allFiles($path) as $file) {
                require $file->getPathname();
            }
        }
    });

/**
 * 🔐 PRIVATE (COM DOMÍNIO)
 */
Route::prefix("api/{$version}")
    ->middleware(['api', 'auth:sanctum'])
    ->group(function () use ($version, $domains) {

        foreach ($domains as $domain) {

            $path = base_path("routes/api/{$version}/private/{$domain}");

            if (is_dir($path)) {

                Route::prefix($domain)->group(function () use ($path) {

                    foreach (File::allFiles($path) as $file) {
                        require $file->getPathname();
                    }
                });
            }
        }
    });
