<?php

use App\Services\AppSettingsService;
use Illuminate\Support\Facades\Route;

Route::get('/', function (AppSettingsService $appSettings) {
    return view('welcome', [
        'appName' => $appSettings->getAppName(),
        'appLogoUrl' => $appSettings->getLogoUrl(),
        'appFaviconUrl' => $appSettings->getFaviconUrl(),
        'appTagline' => $appSettings->getTagline(),
    ]);
})->name('home');

Route::livewire('dashboard', \App\Livewire\Dashboard\Overview::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('subscription/expired', 'subscription-expired')
    ->middleware(['auth'])
    ->name('subscription.expired');

require __DIR__.'/settings.php';
require __DIR__.'/owner.php';
require __DIR__.'/inventory.php';
require __DIR__.'/pos.php';
require __DIR__.'/suppliers.php';
require __DIR__.'/purchase_orders.php';
require __DIR__.'/reports.php';
require __DIR__.'/super-admin.php';
