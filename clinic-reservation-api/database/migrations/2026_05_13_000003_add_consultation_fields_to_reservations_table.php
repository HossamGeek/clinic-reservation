<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table): void {
            if (! Schema::hasColumn('reservations', 'chief_complaint')) {
                $table->text('chief_complaint')->nullable();
            }

            if (! Schema::hasColumn('reservations', 'objective_observations')) {
                $table->text('objective_observations')->nullable();
            }

            if (! Schema::hasColumn('reservations', 'assessment_plan')) {
                $table->text('assessment_plan')->nullable();
            }

            if (! Schema::hasColumn('reservations', 'prescription_info')) {
                $table->json('prescription_info')->nullable();
            }

            if (! Schema::hasColumn('reservations', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Compatibility migration: keep imported clinic data intact on rollback.
    }
};
