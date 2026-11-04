<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_pengurus_can_view_daily_report(): void
    {
        $pengurus = User::where('role', 'pengurus')->firstOrFail();

        $response = $this->actingAs($pengurus)->get(route('admin.reports.daily', [
            'date' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Laporan Rekapitulasi Harian');
        $response->assertSee('Total Perjalanan');
        $response->assertSee('Santri Unik');
    }

    public function test_pengurus_can_export_daily_report_csv(): void
    {
        $pengurus = User::where('role', 'pengurus')->firstOrFail();

        $response = $this->actingAs($pengurus)->get(route('admin.reports.export-csv', [
            'date' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_pengurus_can_view_monthly_report(): void
    {
        $pengurus = User::where('role', 'pengurus')->firstOrFail();

        $response = $this->actingAs($pengurus)->get(route('admin.reports.monthly', [
            'month' => now()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Laporan Rekapitulasi Bulanan');
        $response->assertSee('Santri Unik Bulanan');
    }
}
