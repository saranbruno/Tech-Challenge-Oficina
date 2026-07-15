<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_access_the_protected_route(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ]);

        $login = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ]);

        $token = $login
            ->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'refresh_expires_in',
            ])
            ->json('access_token');

        $this->withToken($token)
            ->getJson('/api/admin/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $admin->id)
            ->assertJsonPath('data.email', 'admin@example.com');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ]);

        $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized()->assertExactJson([
            'error' => [
                'code' => 'invalid_credentials',
                'message' => 'Credenciais invalidas.',
            ],
        ]);
    }

    public function test_login_validates_the_request(): void
    {
        $this->postJson('/api/admin/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonStructure(['error' => ['details' => ['email', 'password']]]);
    }

    public function test_protected_route_rejects_a_missing_token(): void
    {
        $this->getJson('/api/admin/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_protected_route_rejects_an_invalid_token(): void
    {
        $this->withToken('invalid-token')
            ->getJson('/api/admin/auth/me')
            ->assertUnauthorized();
    }

    public function test_expired_token_is_rejected_and_can_be_refreshed(): void
    {
        $issuedAt = Carbon::now();
        Carbon::setTestNow($issuedAt);

        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ]);

        $token = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ])->json('access_token');

        Auth::forgetGuards();
        app(JWTAuth::class)->unsetToken();
        Carbon::setTestNow($issuedAt->copy()->addMinutes(61));

        $this->withToken($token)
            ->getJson('/api/admin/auth/me')
            ->assertUnauthorized();

        Auth::forgetGuards();
        app(JWTAuth::class)->unsetToken();

        $newToken = $this->withToken($token)
            ->postJson('/api/admin/auth/refresh')
            ->assertOk()
            ->json('access_token');

        $this->assertNotSame($token, $newToken);

        Auth::forgetGuards();
        app(JWTAuth::class)->unsetToken();

        $authenticatedAdmin = app(JWTAuth::class)
            ->setToken($newToken)
            ->authenticate();

        $this->assertSame('admin@example.com', $authenticatedAdmin->email);
    }

    public function test_refresh_rejects_missing_and_invalid_tokens(): void
    {
        $this->postJson('/api/admin/auth/refresh')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_token');

        $this->withToken('invalid-token')
            ->postJson('/api/admin/auth/refresh')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_token');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
