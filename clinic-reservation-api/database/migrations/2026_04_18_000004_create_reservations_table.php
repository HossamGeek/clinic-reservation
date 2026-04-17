<!-- <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->increments('reservation_id');
            $table->string('reservation_code', 50)->unique();
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('doctor_id');
            $table->date('appointment_date');
            $table->string('time_slot', 50);
            $table->text('session_details')->nullable();
            $table->text('prescription_info')->nullable();
            $table->enum('status', ['PENDING', 'CONFIRMED', 'CANCELLED', 'COMPLETED'])->default('CONFIRMED');
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('reservation_code', 'idx_reservation_code');
            $table->index('appointment_date', 'idx_appointment_date');
            $table->index('time_slot', 'idx_time_slot');

            $table->foreign('patient_id', 'fk_reservations_patient')
                ->references('patient_id')
                ->on('patients')
                ->cascadeOnDelete();

            $table->foreign('doctor_id', 'fk_reservations_doctor')
                ->references('doctor_id')
                ->on('doctors')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
}; -->
