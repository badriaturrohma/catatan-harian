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
        Schema::create('journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->enum('type', ['reguler', 'izin'])->default('reguler');
            $table->date('operational_date');
            $table->enum('status', [
                'menuju_kampus',
                'di_kampus',
                'menuju_pondok',
                'selesai',
                'rekonsiliasi',
            ])->default('menuju_kampus');
            $table->foreignId('permit_id')->nullable()->constrained('permits')->nullOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->json('organization_snapshot')->nullable();
            $table->boolean('is_incomplete')->default(false);
            $table->text('reconciliation_notes')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'operational_date']);
            $table->index('status');
        });

        Schema::create('journey_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();
            $table->enum('activity', [
                'keluar_pondok',
                'tiba_kampus',
                'keluar_kampus',
                'kembali_pondok',
            ]);
            $table->dateTime('occurred_at');
            $table->dateTime('recorded_at');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->enum('method', ['qr_scan', 'manual', 'reconciliation'])->default('qr_scan');
            $table->string('request_id')->unique();
            $table->boolean('is_late')->default(false);
            $table->unsignedBigInteger('correction_reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['journey_id', 'activity']);
            $table->index('occurred_at');
        });

        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_event_id')->constrained('journey_events')->cascadeOnDelete();
            $table->json('proposed_data');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
        Schema::dropIfExists('journey_events');
        Schema::dropIfExists('journeys');
    }
};
