<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $table = 'doctors';
    protected $primaryKey = 'doctor_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'specialty',
        'rating',
        'available_time',
        'bio',
        'created_at',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'doctor_id', 'doctor_id');
    }
}
