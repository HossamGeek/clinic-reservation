<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);
Route::get('/doctors/{id}/available-slots', [DoctorController::class, 'availableSlots']);
Route::post('/reservations', [ReservationController::class, 'store']);

//if you want to use auth middleware  
// Route::middleware('auth:api')->get('route', [Controller::class, 'functionName']);
