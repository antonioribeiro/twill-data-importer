<?php

use A17\TwillDataImporter\Support\Facades\Route;

// @phpstan-ignore-next-line
Route::name('twillDataImporter.redirectToEdit')->get('/twillDataImporter/redirectToEdit', [
    \A17\TwillDataImporter\Http\Controllers\TwillDataImporterController::class,
    'redirectToEdit',
]);

// @phpstan-ignore-next-line
Route::module('twillDataImporter');
