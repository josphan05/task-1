<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest','nocache'])->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});


Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook')
    ->withoutMiddleware(['web']);


Route::get('/telegram/setup-webhook', [TelegramWebhookController::class, 'setupWebhook'])->name('telegram.setup-webhook');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);

    // Telegram
    Route::get('/telegram', [TelegramController::class, 'index'])->name('telegram.index');
    Route::post('/telegram/send', [TelegramController::class, 'send'])->name('telegram.send');
    Route::get('/telegram/responses', [TelegramController::class, 'responses'])->name('telegram.responses');

    Route::get('/telegram/callbacks', [TelegramWebhookController::class, 'getCallbacks'])->name('telegram.callbacks');
    Route::get('/telegram/callbacks/new', [TelegramWebhookController::class, 'getNewCallbacks'])->name('telegram.callbacks.new');
    Route::get('/telegram/messages', [TelegramWebhookController::class, 'getMessages'])->name('telegram.messages');
    Route::get('/telegram/messages/new', [TelegramWebhookController::class, 'getNewMessages'])->name('telegram.messages.new');
});
