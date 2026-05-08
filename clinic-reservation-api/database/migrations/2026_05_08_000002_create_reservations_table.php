<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable()->index();
            $table->unsignedBigInteger('doctor_id')->index();
            $table->string('reservation_code', 32)->unique();
            $table->date('reservation_date');
            $table->string('time_slot', 20);
            $table->string('status', 20)->default('confirmed')->index();
            $table->string('patient_name', 150);
            $table->string('patient_email', 150);
            $table->string('patient_phone', 30);
            $table->text('notes')->nullable();
            $table->decimal('consultation_fee', 8, 2)->nullable()->default(150);
            $table->decimal('processing_fee', 8, 2)->nullable()->default(5);
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'reservation_date', 'time_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
