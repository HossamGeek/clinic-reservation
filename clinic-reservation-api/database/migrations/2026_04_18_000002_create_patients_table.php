<!-- <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->increments('patient_id');
            $table->unsignedInteger('user_id')->unique();
            $table->enum('gender', ['MALE', 'FEMALE'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address', 255)->nullable();
            $table->text('medical_history')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->foreign('user_id', 'fk_patients_user')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
}; -->
