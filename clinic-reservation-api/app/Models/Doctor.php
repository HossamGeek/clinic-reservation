<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $table = 'doctors';

    protected $fillable = [
        'user_id',
        'name',
        'specialty',
        'bio',
        'rating',
        'available_time',
        'location',
        'image',
        'consultation_fee',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'consultation_fee' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'doctor_id', 'id');
    }

    public function getDoctorIdAttribute(mixed $value): mixed
    {
        return $value ?? $this->attributes['id'] ?? null;
    }
}
