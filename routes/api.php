<?php
// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UpdateController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('updates')->group(function () {
    Route::get('/languages', [UpdateController::class, 'getLanguageUpdates']);
    Route::get('/libraries', [UpdateController::class, 'getLibraryUpdates']);
    Route::get('/package/{package}', [UpdateController::class, 'getPackageUpdates']);
    Route::get('/github/{owner}/{repo}', [UpdateController::class, 'getGitHubUpdates']);
});
