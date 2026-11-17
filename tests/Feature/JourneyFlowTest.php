<?php

namespace Tests\Feature;

use App\Models\OfficerAssignment;
use App\Models\Santri;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_officer_can_resolve_valid_qr_token(): void
    {
        $officer = User::where('role', 'petugas')->firstOrFail();
        $assignment = OfficerAssignment::where('user_id', $officer->id)->firstOrFail();
        $santri = Santri::where('is_active', true)->firstOrFail();
        $credential = $santri->activeQrCredential;

        $plainToken = 'SNT-TEST1234567890';
        $credential->update([
            'token_hash' => hash('sha256', $plainToken),
            'is_active' => true,
        ]);

        $response = $this->actingAs($officer)->postJson(route('officer.qr.resolve'), [
            'token' => $plainToken,
            'assignment_id' => $assignment->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'santri' => [
                    'id' => $santri->id,
                    'name' => $santri->name,
                ],
            ],
        ]);
    }

    public function test_idempotent_event_recording_does_not_create_duplicates(): void
    {
        $officer = User::where('role', 'petugas')->firstOrFail();
        $assignment = OfficerAssignment::where('user_id', $officer->id)->firstOrFail();
        $santri = Santri::where('is_active', true)->firstOrFail();
        $credential = $santri->activeQrCredential;

        $requestId = 'TEST-REQ-'.uniqid();

        // First attempt: should record successfully
        $res1 = $this->actingAs($officer)->postJson(route('officer.events.record'), [
            'credential_id' => $credential->id,
            'assignment_id' => $assignment->id,
            'activity' => 'keluar_pondok',
            'request_id' => $requestId,
        ]);

        $res1->assertStatus(200);
        $res1->assertJson(['status' => 'success']);

        // Second attempt with exact same request_id: should return duplicate idempotently without creating duplicate
        $res2 = $this->actingAs($officer)->postJson(route('officer.events.record'), [
            'credential_id' => $credential->id,
            'assignment_id' => $assignment->id,
            'activity' => 'keluar_pondok',
            'request_id' => $requestId,
        ]);

        $res2->assertStatus(200);
        $res2->assertJson([
            'status' => 'duplicate',
        ]);
    }

    public function test_guardian_only_sees_verified_children(): void
    {
        $wali = User::where('role', 'wali')->firstOrFail();
        $response = $this->actingAs($wali)->get(route('parent.dashboard'));

        $response->assertStatus(200);
        $verifiedChild = $wali->verifiedChildren()->firstOrFail();
        $response->assertSee($verifiedChild->name);

        // Verify unverified santri is not exposed
        $unrelatedSantri = Santri::where('id', '!=', $verifiedChild->id)->first();
        if ($unrelatedSantri) {
            $response->assertDontSee($unrelatedSantri->name);
        }
    }

    public function test_guardian_can_claim_santri_using_valid_niup(): void
    {
        $wali = User::where('role', 'wali')->firstOrFail();
        $unclaimedSantri = Santri::whereDoesntHave('guardians', function ($q) use ($wali) {
            $q->where('users.id', $wali->id);
        })->firstOrFail();

        $response = $this->actingAs($wali)->post(route('parent.claim-santri'), [
            'niup' => $unclaimedSantri->niup,
            'relationship_type' => 'Ayah',
        ]);

        $response->assertRedirect(route('parent.dashboard', ['santri_id' => $unclaimedSantri->id]));
        $response->assertSessionHas('success');

        $this->assertTrue($wali->fresh()->santris->contains('id', $unclaimedSantri->id));
    }

    public function test_guardian_cannot_claim_non_existent_niup(): void
    {
        $wali = User::where('role', 'wali')->firstOrFail();

        $response = $this->actingAs($wali)->post(route('parent.claim-santri'), [
            'niup' => 'NIUP-TIDAK-ADA-999999',
            'relationship_type' => 'Wali',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_guardian_cannot_claim_same_santri_twice(): void
    {
        $wali = User::where('role', 'wali')->firstOrFail();
        $connectedSantri = $wali->santris()->firstOrFail();

        $response = $this->actingAs($wali)->post(route('parent.claim-santri'), [
            'niup' => $connectedSantri->niup,
            'relationship_type' => 'Wali',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_officer_can_resolve_qr_by_token_hash(): void
    {
        $officer = User::where('role', 'petugas')->firstOrFail();
        $assignment = OfficerAssignment::where('user_id', $officer->id)->firstOrFail();
        $santri = Santri::where('is_active', true)->firstOrFail();
        $credential = $santri->activeQrCredential;

        $response = $this->actingAs($officer)->postJson(route('officer.qr.resolve'), [
            'token' => $credential->token_hash,
            'assignment_id' => $assignment->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'santri' => [
                    'id' => $santri->id,
                    'name' => $santri->name,
                ],
            ],
        ]);
    }

    public function test_officer_can_resolve_santri_by_niup_fallback(): void
    {
        $officer = User::where('role', 'petugas')->firstOrFail();
        $assignment = OfficerAssignment::where('user_id', $officer->id)->firstOrFail();
        $santri = Santri::where('is_active', true)->firstOrFail();

        $response = $this->actingAs($officer)->postJson(route('officer.qr.resolve'), [
            'token' => $santri->niup,
            'assignment_id' => $assignment->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'santri' => [
                    'id' => $santri->id,
                    'name' => $santri->name,
                ],
            ],
        ]);
    }

    public function test_pengurus_can_access_scanner_and_resolve_qr(): void
    {
        $pengurus = User::where('role', 'pengurus')->firstOrFail();
        $santri = Santri::where('is_active', true)->firstOrFail();
        $credential = $santri->activeQrCredential;

        // Pengurus visits scan page
        $pageResponse = $this->actingAs($pengurus)->get(route('officer.scan'));
        $pageResponse->assertStatus(200);

        // Pengurus resolves QR code
        $resolveResponse = $this->actingAs($pengurus)->postJson(route('officer.qr.resolve'), [
            'token' => $credential->token_hash,
        ]);

        $resolveResponse->assertStatus(200);
        $resolveResponse->assertJson([
            'success' => true,
            'data' => [
                'santri' => [
                    'id' => $santri->id,
                    'name' => $santri->name,
                ],
            ],
        ]);
    }
}
