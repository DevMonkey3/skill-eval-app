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

    Route::resource('employees', EmployeeController::class);
    Route::resource('skills', SkillController::class)->except('show');
    Route::resource('evaluations', EvaluationController::class)->except('show');

    Route::post('employees/{employee}/skills', [EmployeeSkillController::class, 'store'])
        ->name('employees.skills.store');
    Route::delete('employees/{employee}/skills/{skill}', [EmployeeSkillController::class, 'destroy'])
        ->name('employees.skills.destroy');
});

require __DIR__.'/settings.php';
