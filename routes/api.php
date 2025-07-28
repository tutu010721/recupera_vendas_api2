<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// === NOSSAS ROTAS DE WEBHOOK ATUALIZADAS ===
// Aponta tanto GET (para validação) quanto POST (para dados) para o mesmo lugar
Route::match(['get', 'post'], '/webhook/{platform}/{key}', [WebhookController::class, 'handle']);
