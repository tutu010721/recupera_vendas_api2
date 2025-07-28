use App\Http\Controllers\Api\WebhookController;
Route::post('/webhook/{platform}/{key}', [WebhookController::class, 'handle']);
