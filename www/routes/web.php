<?php

use App\Http\Controllers\GameController;
use App\Services\GameService;
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

Route::middleware('auth')->group(function () {
    Route::get('/', [GameController::class, 'defaultAction'])->name('game.default');
    Route
        ::post('/{piece}', [GameController::class, 'makeMoveAction'])
        ->name('game.makeMove')
        ->whereIn('piece', GameService::PIECES)
    ;
    Route::post('/restart', [GameController::class, 'restartAction'])->name('game.restart');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
