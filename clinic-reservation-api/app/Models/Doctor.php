<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Doctor extends Model
{
    protected $table = 'doctors';
    protected $primaryKey = 'doctor_id';
    protected $keyType = 'int';
    public $incrementing = true;

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
        'reviews_count',
        'accepts_new_patients',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'consultation_fee' => 'decimal:2',
        'reviews_count' => 'integer',
        'accepts_new_patients' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getKeyName()
    {
        return self::keyColumn();
    }

    public static function keyColumn(): string
    {
        try {
            return Schema::hasColumn((new self())->getTable(), 'doctor_id') ? 'doctor_id' : 'id';
        } catch (Throwable) {
            return 'doctor_id';
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'doctor_id', $this->getKeyName());
    }

    public function getDoctorIdAttribute(mixed $value): mixed
    {
        return $value ?? $this->attributes['id'] ?? null;
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->attributes['name'] ?? null;

        if (filled($name)) {
            return $name;
        }

        $userName = null;

        try {
            if (Schema::hasTable('users') && filled($this->attributes['user_id'] ?? null)) {
                $userName = optional($this->user)->full_name;
            }
        } catch (Throwable) {
            $userName = null;
        }

        if (filled($userName)) {
            return $userName;
        }

        $doctorId = $this->attributes['doctor_id'] ?? $this->attributes['id'] ?? null;

        return $doctorId ? 'Doctor '.$doctorId : 'Doctor';
    }

    public function locationForBooking(): string
    {
        return filled($this->attributes['location'] ?? null)
            ? $this->attributes['location']
            : 'Main City Hospital, Building A, Suite 302';
    }

    public function consultationFeeForBooking(): float
    {
        $fee = $this->attributes['consultation_fee'] ?? null;

        return is_numeric($fee) ? (float) $fee : 150.0;
    }

    public function imageForBooking(): ?string
    {
        $image = $this->attributes['image'] ?? null;

        return filled($image) ? $image : null;
    }
}
