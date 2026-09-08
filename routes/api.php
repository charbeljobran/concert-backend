<?php

use App\Http\Controllers\PriceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tables', TableController::class)->only(['index', 'show', 'store']);

Route::get('reservations/years', [ReservationController::class, 'years']);
Route::apiResource('reservations', ReservationController::class);
Route::post('reservations/{id}/tables', [ReservationController::class, 'addTables']);
Route::delete('reservations/{id}/tables/{tableId}', [ReservationController::class, 'removeTable']);
Route::get('prices', [PriceController::class, 'index']);
Route::post('prices', [PriceController::class, 'store']);
