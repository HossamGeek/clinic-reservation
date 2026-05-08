<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table): void {
                $table->id('reservation_id');
                $table->unsignedBigInteger('patient_id')->nullable()->index();
                $table->unsignedBigInteger('doctor_id')->index();
                $table->string('reservation_code', 32)->nullable()->unique();
                $table->date('appointment_date');
                $table->string('time_slot', 20);
                $table->string('status', 20)->default('confirmed')->index();
                $table->string('patient_name', 150)->nullable();
                $table->string('patient_email', 150)->nullable();
                $table->string('patient_phone', 30)->nullable();
                $table->text('notes')->nullable();
                $table->decimal('consultation_fee', 8, 2)->nullable()->default(150);
                $table->decimal('processing_fee', 8, 2)->nullable()->default(5);
                $table->string('location')->nullable();
                $table->timestamps();

                $table->index(['doctor_id', 'appointment_date', 'time_slot']);
            });

            return;
        }

        $hadReservationDate = Schema::hasColumn('reservations', 'reservation_date');
        $addedAppointmentDate = false;

        Schema::table('reservations', function (Blueprint $table) use ($hadReservationDate, &$addedAppointmentDate): void {
            if (! Schema::hasColumn('reservations', 'patient_id')) {
                $table->unsignedBigInteger('patient_id')->nullable()->index();
            }

            if (! Schema::hasColumn('reservations', 'doctor_id')) {
                $table->unsignedBigInteger('doctor_id')->nullable()->index();
            }

            if (! Schema::hasColumn('reservations', 'reservation_code')) {
                $table->string('reservation_code', 32)->nullable()->unique();
            }

            if (! Schema::hasColumn('reservations', 'appointment_date')) {
                $table->date('appointment_date')->nullable();
                $addedAppointmentDate = true;
            }

            if (! Schema::hasColumn('reservations', 'time_slot')) {
                $table->string('time_slot', 20)->nullable();
            }

            if (! Schema::hasColumn('reservations', 'status')) {
                $table->string('status', 20)->default('confirmed')->index();
            }

            if (! Schema::hasColumn('reservations', 'patient_name')) {
                $table->string('patient_name', 150)->nullable();
            }

            if (! Schema::hasColumn('reservations', 'patient_email')) {
                $table->string('patient_email', 150)->nullable();
            }

            if (! Schema::hasColumn('reservations', 'patient_phone')) {
                $table->string('patient_phone', 30)->nullable();
            }

            if (! Schema::hasColumn('reservations', 'notes')) {
                $table->text('notes')->nullable();
            }

            if (! Schema::hasColumn('reservations', 'consultation_fee')) {
                $table->decimal('consultation_fee', 8, 2)->nullable()->default(150);
            }

            if (! Schema::hasColumn('reservations', 'processing_fee')) {
                $table->decimal('processing_fee', 8, 2)->nullable()->default(5);
            }

            if (! Schema::hasColumn('reservations', 'location')) {
                $table->string('location')->nullable();
            }

            if (! Schema::hasColumn('reservations', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('reservations', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if ($addedAppointmentDate && $hadReservationDate) {
            DB::table('reservations')
                ->whereNull('appointment_date')
                ->update(['appointment_date' => DB::raw('reservation_date')]);
        }
    }

    public function down(): void
    {
        // Compatibility migration: do not drop imported legacy tables on rollback.
    }
};
