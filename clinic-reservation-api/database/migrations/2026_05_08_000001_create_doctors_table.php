<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name', 150)->nullable();
            $table->string('specialty', 100);
            $table->text('bio')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->string('available_time', 150)->nullable();
            $table->string('location')->nullable();
            $table->string('image')->nullable();
            $table->decimal('consultation_fee', 8, 2)->nullable()->default(150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
