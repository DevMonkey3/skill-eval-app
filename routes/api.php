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

    // 画面と同じく、メール認証が済んだユーザーだけが使える
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::delete('tokens/current', [TokenController::class, 'destroy'])->name('tokens.destroy');

        Route::get('user', fn (Request $request) => $request->user())->name('user');

        // 社員の登録・更新・削除は管理者だけ
        Route::apiResource('employees', EmployeeController::class)
            ->except(['index', 'show'])
            ->middleware('can:admin');
        Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);

        // 評価は誰でも登録できる。更新・削除は EvaluationPolicy で本人だけに絞る
        Route::apiResource('evaluations', EvaluationController::class);
    });
});
