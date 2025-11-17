<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\ImageGenerationController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function(){
Route::apiResource('image-generations', ImageGenerationController::class)->only(['index', 'store']);
});
require __DIR__.'/auth.php';