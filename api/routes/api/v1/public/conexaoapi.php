<?php

use Illuminate\Support\Facades\Route;

/*

|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/conexao-api', function () {
    // Instancia uma classe anônima para poder usar a Trait inline sem precisar de um Controller dedicado
    $jsonResponse = new class {
        use \App\Traits\TraitReturnJsonOlirum; // Certifique-se de que o namespace da sua Trait está correto aqui
    };

    $data = [
        'app_name'    => config('app.name'),
        'environment' => config('app.env'),
        'database'    => 'connected',
        'timestamp'   => now()->toIso8601String(),
    ];

    // Retorna a resposta estruturada utilizando os parâmetros exatos da sua Trait
    return $jsonResponse->ReturnJson(
        $data, 
        'Conexão com a API estabelecida com sucesso.', 
        true, 
        200
    );
});
