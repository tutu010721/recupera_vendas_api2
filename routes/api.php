<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController; // Importar nosso controller

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// === NOSSAS ROTAS DE WEBHOOK (ADICIONAR AQUI) ===

// Rota para a validação do webhook (geralmente um GET)
Route::get('/webhook/{platform}/{key}', function () {
    return response()->json(['status' => 'webhook url is valid and ready']);
});

// Nossa rota principal que processa os dados (POST)
Route::post('/webhook/{platform}/{key}', [WebhookController::class, 'handle']);
