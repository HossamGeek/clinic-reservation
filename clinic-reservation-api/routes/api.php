<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//if you want to use auth middleware  
// Route::middleware('auth:api')->get('route', [Controller::class, 'functionName']);
