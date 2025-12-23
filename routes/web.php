<?php

use App\Http\Controllers\Api\BotController;
use App\Http\Controllers\Api\ExchangeAccountController;
use App\Http\Controllers\Api\StrategyController;
use App\Models\Bot;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/exchange-accounts', function () {
        return Inertia::render('ExchangeAccounts/Index');
    })->name('exchange-accounts.index');
    Route::get('/strategies', function () {
        return Inertia::render('Strategies/Index');
    })->name('strategies.index');

    Route::get('/bots', function () {
        return Inertia::render('Bots/Index');
    })->name('bots.index');
        Route::get('/bots/{bot}', function (Bot $bot) {
        return Inertia::render('Bots/Show', [
            'botId' => $bot->id,
        ]);
    })->name('bots.show');
     Route::prefix('api')->group(function () {
            Route::apiResource('exchange-accounts', ExchangeAccountController::class);
        Route::apiResource('strategies', StrategyController::class);
        Route::apiResource('bots', BotController::class);
            Route::get('bots/{bot}/loop-data', [BotController::class, 'loopData']);
                Route::get('bots/{bot}/position', [BotController::class, 'position']);

Route::post('bots/{bot}/start', [BotController::class, 'start']);
    Route::post('bots/{bot}/pause', [BotController::class, 'pause']);
    Route::post('bots/{bot}/stop', [BotController::class, 'stop']);
        Route::get('bots/{bot}/stats', [BotController::class, 'stats']);

        // aquí después agregaremos strategies, bots, etc.
    });
});
require __DIR__.'/settings.php';
