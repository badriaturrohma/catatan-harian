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
        Schema::create('alert_episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained('journeys')->cascadeOnDelete();
            $table->enum('type', [
                'belum_tiba_kampus',
                'belum_kembali_reguler',
                'belum_kembali_izin',
            ]);
            $table->enum('highest_level', ['kuning', 'merah'])->default('kuning');
            $table->dateTime('threshold_at');
            $table->dateTime('detected_at');
            $table->dateTime('resolved_at')->nullable();
            $table->enum('resolution_kind', [
                'event_tercatat',
                'koreksi',
                'manual',
                'rekonsiliasi',
                'pengurus_decision',
            ])->nullable();
            $table->timestamps();

            $table->index(['journey_id', 'resolved_at']);
        });

        Schema::create('alert_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('episode_id')->constrained('alert_episodes')->cascadeOnDelete();
            $table->foreignId('rule_version_id')->nullable()->constrained('rule_versions')->nullOnDelete();
            $table->dateTime('evaluated_at');
            $table->string('state');
            $table->text('change_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('episode_id')->constrained('alert_episodes')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->text('notes');
            $table->timestamps();
        });

        Schema::create('report_snapshots', function (Blueprint $table) {
            $table->id();
            $table->enum('period_type', ['daily', 'monthly']);
            $table->string('period_key'); // 'YYYY-MM-DD' or 'YYYY-MM'
            $table->string('scope')->default('global');
            $table->unsignedInteger('revision')->default(1);
            $table->string('source_watermark')->nullable();
            $table->json('totals');
            $table->enum('status', ['sementara', 'final', 'revisi'])->default('sementara');
            $table->dateTime('generated_at');
            $table->unsignedBigInteger('supersedes_id')->nullable();
            $table->text('revision_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period_type', 'period_key', 'scope', 'revision'], 'report_snap_idx');
        });

        Schema::create('report_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('report_snapshots')->cascadeOnDelete();
            $table->foreignId('journey_id')->nullable()->constrained('journeys')->nullOnDelete();
            $table->foreignId('santri_id')->nullable()->constrained('santris')->nullOnDelete();
            $table->json('historical_summary');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('report_rows');
        Schema::dropIfExists('report_snapshots');
        Schema::dropIfExists('followups');
        Schema::dropIfExists('alert_evaluations');
        Schema::dropIfExists('alert_episodes');
    }
};
