<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Monitoring Santri');
    }

    public function test_users_can_authenticate_and_redirect_by_role(): void
    {
        $pengurus = User::where('role', 'pengurus')->firstOrFail();

        $response = $this->post('/login', [
            'email' => $pengurus->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_officer_redirects_to_scanner(): void
    {
        $petugas = User::where('role', 'petugas')->firstOrFail();

        $response = $this->post('/login', [
            'email' => $petugas->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('officer.scan'));
    }
}
