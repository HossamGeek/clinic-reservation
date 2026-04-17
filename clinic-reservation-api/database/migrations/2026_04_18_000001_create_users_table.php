<!-- <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('full_name', 150);
            $table->string('email', 150)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password_hash', 255);
            $table->enum('role', ['PATIENT', 'DOCTOR']);
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('full_name', 'idx_users_full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}; -->
