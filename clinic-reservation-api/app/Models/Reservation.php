<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';
    public $timestamps = false;

    protected $fillable = [
        'reservation_code',
        'patient_id',
        'doctor_id',
        'appointment_date',
        'time_slot',
        'session_details',
        'prescription_info',
        'status',
        'created_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
}
