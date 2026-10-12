<?php

use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('pu.result'));

// Question 1 - individual polling unit result
Route::get('/polling-unit', [ResultController::class, 'pollingUnit'])->name('pu.result');

// Question 2 - summed result of every polling unit under an LGA
Route::get('/lga-summary', [ResultController::class, 'lgaSummary'])->name('lga.summary');

// Question 3 - store results for all parties of a new polling unit
Route::get('/new-polling-unit', [ResultController::class, 'create'])->name('pu.create');
Route::post('/new-polling-unit', [ResultController::class, 'store'])->name('pu.store');

// Chained combo box helpers
Route::get('/ajax/wards/{lgaId}', [ResultController::class, 'wards'])->name('ajax.wards');
Route::get('/ajax/units/{wardUniqueid}', [ResultController::class, 'units'])->name('ajax.units');
