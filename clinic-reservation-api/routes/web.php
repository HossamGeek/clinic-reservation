<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/patient/book-appointment/{doctorId?}', function (?int $doctorId = null) {
    return view('patient.book-appointment', [
        'doctorId' => $doctorId,
    ]);
})->whereNumber('doctorId')->name('patient.book-appointment');

Route::get('/provider/active-consultation/{patientId}/{doctorId}', function (int $patientId, int $doctorId) {
    return view('provider.active-consultation', [
        'patientId' => $patientId,
        'doctorId' => $doctorId,
    ]);
})->whereNumber('patientId')->whereNumber('doctorId')->name('provider.active-consultation');
