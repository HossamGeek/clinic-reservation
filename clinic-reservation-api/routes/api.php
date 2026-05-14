<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\PatientRecordController;
use App\Http\Controllers\Api\ProviderReservationController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);
Route::get('/doctors/{id}/available-slots', [DoctorController::class, 'availableSlots']);
Route::get('/patient/record', [PatientRecordController::class, 'record']);
Route::get('/patient/appointments', [PatientRecordController::class, 'appointments']);
Route::get('/provider/reservations', [ProviderReservationController::class, 'index']);
Route::get('/provider/active-consultation/{patientId}/{doctorId}', [ProviderReservationController::class, 'activeConsultation'])
    ->whereNumber(['patientId', 'doctorId']);
Route::get('/reservations/active-consultation', [ReservationController::class, 'activeConsultation']);
Route::post('/reservations/active-consultation', [ReservationController::class, 'createActiveConsultation']);
Route::get('/reservations/{id}/consultation', [ReservationController::class, 'showConsultation'])->whereNumber('id');
Route::patch('/reservations/{id}/consultation', [ReservationController::class, 'updateConsultation'])->whereNumber('id');
Route::patch('/reservations/{id}/complete', [ReservationController::class, 'completeConsultation'])->whereNumber('id');
Route::post('/reservations', [ReservationController::class, 'store']);
Route::patch('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);

//if you want to use auth middleware  
// Route::middleware('auth:api')->get('route', [Controller::class, 'functionName']);
