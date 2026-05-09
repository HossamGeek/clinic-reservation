<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctors')) {
            Schema::create('doctors', function (Blueprint $table): void {
                $table->id('doctor_id');
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

            return;
        }

        Schema::table('doctors', function (Blueprint $table): void {
            if (! Schema::hasColumn('doctors', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index();
            }

            if (! Schema::hasColumn('doctors', 'specialty')) {
                $table->string('specialty', 100)->nullable();
            }

            if (! Schema::hasColumn('doctors', 'bio')) {
                $table->text('bio')->nullable();
            }

            if (! Schema::hasColumn('doctors', 'rating')) {
                $table->decimal('rating', 2, 1)->nullable();
            }

            if (! Schema::hasColumn('doctors', 'available_time')) {
                $table->string('available_time', 150)->nullable();
            }

            if (! Schema::hasColumn('doctors', 'location')) {
                $table->string('location')->nullable();
            }

            if (! Schema::hasColumn('doctors', 'image')) {
                $table->string('image')->nullable();
            }

            if (! Schema::hasColumn('doctors', 'consultation_fee')) {
                $table->decimal('consultation_fee', 8, 2)->nullable()->default(150);
            }

            if (! Schema::hasColumn('doctors', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('doctors', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Compatibility migration: do not drop imported legacy tables on rollback.
    }
};
