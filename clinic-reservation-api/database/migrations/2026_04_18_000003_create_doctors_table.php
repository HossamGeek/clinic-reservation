<!-- <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->increments('doctor_id');
            $table->unsignedInteger('user_id')->unique();
            $table->string('specialty', 100);
            $table->decimal('rating', 2, 1)->default(0.0);
            $table->string('available_time', 100)->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('specialty', 'idx_doctors_specialty');
            $table->foreign('user_id', 'fk_doctors_user')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
}; -->
