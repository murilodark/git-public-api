<?php


use App\Exceptions\ApiExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\File;
use App\Http\Middleware\ForceJsonResponse;



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {

            $path = base_path('routes/api');

            if (is_dir($path)) {

                foreach (File::files($path) as $file) {

                    if (preg_match('/^v\d+\.php$/', $file->getFilename())) {
                        require $file->getPathname();
                    }
                }
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(new ApiExceptionHandler()) //trata as exceções de forma personalizada para a API
    
    ->create();