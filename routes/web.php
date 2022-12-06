<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MigrarPerguntaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::get('/perguntas', function () {
    return view('perguntas');
})->middleware(['auth.token'])->name('perguntas');

Route::middleware('auth.token')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/migrar-perguntas', [MigrarPerguntaController::class, 'store'])->name('migrar.pergunta');
    Route::post('migrar-lote', [MigrarPerguntaController::class, 'lote'])->name('migrar.pergunta.lote');
    Route::get('/retry', [MigrarPerguntaController::class, 'replyMigratedQuestions'])->name('retry');
});

require __DIR__.'/auth.php';
