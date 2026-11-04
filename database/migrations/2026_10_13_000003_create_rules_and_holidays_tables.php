<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rule_versions', function (Blueprint $table) {
            $table->id();
            $table->enum('scope', ['global', 'campus'])->default('global');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->json('parameters');
            $table->dateTime('effective_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('previous_version_id')->nullable();
            $table->text('change_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('holiday_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('departure_cutoff_time');
            $table->boolean('is_active')->default(true);
            $table->foreignId('rule_version_id')->nullable()->constrained('rule_versions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->enum('destination_type', ['kampus', 'non_kampus'])->default('kampus');
            $table->string('destination_name');
            $table->dateTime('start_time');
            $table->unsignedInteger('duration_hours')->default(3);
            $table->dateTime('deadline');
            $table->text('reason');
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'used',
                'completed',
                'expired',
            ])->default('pending');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permits');
        Schema::dropIfExists('holiday_periods');
        Schema::dropIfExists('rule_versions');
    }
};
