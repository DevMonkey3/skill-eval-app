<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSkillController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // 社員・スキルの登録 / 編集 / 削除は管理者だけ (Gate 'admin' は AppServiceProvider で定義)
    Route::middleware('can:admin')->group(function () {
        Route::resource('employees', EmployeeController::class)->except(['index', 'show']);
        Route::resource('skills', SkillController::class)->except(['index', 'show']);

        Route::post('employees/{employee}/skills', [EmployeeSkillController::class, 'store'])
            ->name('employees.skills.store');
        Route::delete('employees/{employee}/skills/{skill}', [EmployeeSkillController::class, 'destroy'])
            ->name('employees.skills.destroy');
    });

    // 閲覧と評価の登録は全員ができる。評価の編集・削除は EvaluationPolicy で本人だけに絞る
    Route::resource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::get('skills', [SkillController::class, 'index'])->name('skills.index');
    Route::resource('evaluations', EvaluationController::class)->except('show');
});

require __DIR__.'/settings.php';
