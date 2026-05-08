<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $table = 'reservations';

    protected $fillable = [
        'reservation_code',
        'patient_id',
        'doctor_id',
        'reservation_date',
        'time_slot',
        'status',
        'patient_name',
        'patient_email',
        'patient_phone',
        'notes',
        'consultation_fee',
        'processing_fee',
        'location',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'consultation_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function getReservationIdAttribute(mixed $value): mixed
    {
        return $value ?? $this->attributes['id'] ?? null;
    }
}
