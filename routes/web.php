<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::prefix('auth')->group(function () {
    Route::get('login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('auth.login');
    Route::post('login', [App\Http\Controllers\AuthController::class, 'login'])->name('auth.login.post');
    Route::get('logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('auth.logout');
});

Route::prefix('teacher')->group(function () {
    Route::get('/', [App\Http\Controllers\TeacherController::class, 'index'])->name('teacher.index');
    Route::get('create', [App\Http\Controllers\TeacherController::class, 'create'])->name('teacher.create');
    Route::post('store', [App\Http\Controllers\TeacherController::class, 'store'])->name('teacher.store');
    Route::post('{id}/delete', [App\Http\Controllers\TeacherController::class, 'destroy'])->name('teacher.destroy');
});

Route::prefix('parent')->group(function () {
    Route::get('/', [App\Http\Controllers\ParentController::class, 'index'])->name('parent.index');
    Route::get('{code}', [App\Http\Controllers\ParentController::class, 'showQuery'])->name('parent.query');
    Route::post('send-code', [App\Http\Controllers\ParentController::class, 'sendCode'])->name('parent.sendCode');
    Route::post('query', [App\Http\Controllers\ParentController::class, 'query'])->name('parent.query.post');
});