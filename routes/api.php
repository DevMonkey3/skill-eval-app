<?php

use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ルート名には "api." を付けて、画面側の employees.index などと名前がぶつからないようにする
Route::name('api.')->group(function () {
    // パスワード総当たりを防ぐため 1 分に 10 回まで
    Route::post('tokens', [TokenController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('tokens.store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('tokens/current', [TokenController::class, 'destroy'])->name('tokens.destroy');

        Route::get('user', fn (Request $request) => $request->user())->name('user');

        Route::apiResource('employees', EmployeeController::class);
        Route::apiResource('evaluations', EvaluationController::class);
    });
});
