<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Versioned JSON API (public reads + future webhooks/mobile clients)
|--------------------------------------------------------------------------
| Documented via OpenAPI/Swagger (see docs/API_ENDPOINTS.md and l5-swagger).
| Populated across the Living, Fashion, and Audit/Swagger build steps.
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    require __DIR__.'/modules/api_living.php';
    require __DIR__.'/modules/api_fashion.php';
});
