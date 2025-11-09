<?php

use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuizzesController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('groups')->group(function () {
        Route::post('/{group}', [GroupController::class, 'generateReport']);
    });

    Route::prefix('quizzes')->group(function () {
        Route::get('/', [QuizzesController::class, 'index']);
        Route::get('/{quiz}/results/{student}', [QuizzesController::class, 'getResultsByQuiz']);
        Route::get('/{quiz}', [QuizzesController::class, 'getQuiz']);
        Route::get('/discipline/{discipline}/questions', [QuizzesController::class, 'listAllQuestionsByDiscipline']);
        Route::post('/', [QuizzesController::class, 'create']);
        Route::post('/submit', [QuizzesController::class, 'submit']);
        Route::post('{group}/generate-group-report', [QuizzesController::class, 'generateGroupReport']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'listProducts']);
        Route::post('/buy', [ProductController::class, 'buy']);
    });

    Route::get('/disciplines', [DisciplineController::class, 'index']);
    Route::get('/students/{student}', [StudentController::class, 'show']);
});
