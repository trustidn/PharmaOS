<?php

use Illuminate\Support\Facades\Route;

test('registration is disabled - register route not available', function () {
    expect(Route::has('register'))->toBeFalse();
});

test('registration store route is not available', function () {
    expect(Route::has('register.store'))->toBeFalse();
});
