<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityAndCsrfTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/test-csrf-exception', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        });
    }

    public function test_csrf_token_route_returns_json(): void
    {
        $response = $this->getJson(route('csrf.token'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    public function test_token_mismatch_exception_json_response(): void
    {
        $response = $this->postJson('/test-csrf-exception', []);

        $response->assertStatus(419);
        $response->assertJson([
            'message' => 'Sesi keamanan formulir telah kedaluwarsa. Silakan muat ulang halaman.',
        ]);
    }

    public function test_token_mismatch_exception_web_redirect(): void
    {
        $response = $this->from('/login')->post('/test-csrf-exception', [
            'email' => 'test@kafejawa.local',
            'password' => 'secret123',
            '_token' => 'invalid-token',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Sesi keamanan formulir telah kedaluwarsa. Halaman telah disegarkan dengan sesi baru, silakan kirim ulang formulir.');
        $response->assertSessionHasInput('email', 'test@kafejawa.local');
        $response->assertSessionMissing('_old_input.password');
        $response->assertSessionMissing('_old_input._token');
    }

    public function test_login_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'invalid@kafejawa.local',
                'password' => 'wrongpass',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'invalid@kafejawa.local',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(429);
    }

    public function test_session_configuration(): void
    {
        $this->assertTrue(config('session.domain') === null || config('session.domain') === '');
        $this->assertFalse(config('session.secure'));
    }
}
