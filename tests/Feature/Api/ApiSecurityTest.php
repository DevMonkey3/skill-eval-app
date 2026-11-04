<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/**
 * API でも画面のログインと同じ条件 (メール認証・二要素認証) を求める。
 */
class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_is_not_issued_before_the_email_is_verified()
    {
        $user = User::factory()->unverified()->create();

        $this->postJson('/api/tokens', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertCount(0, $user->tokens);
    }

    public function test_unverified_users_cannot_use_an_existing_token()
    {
        Sanctum::actingAs(User::factory()->unverified()->create());

        $this->getJson('/api/employees')->assertForbidden();
    }

    public function test_two_factor_users_need_a_valid_code_to_get_a_token()
    {
        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();

        $user = User::factory()->create([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $credentials = ['email' => $user->email, 'password' => 'password', 'device_name' => 'test'];

        $this->postJson('/api/tokens', $credentials)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->postJson('/api/tokens', [...$credentials, 'code' => '000000'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->postJson('/api/tokens', [...$credentials, 'code' => $google2fa->getCurrentOtp($secret)])
            ->assertCreated();

        $this->assertCount(1, $user->tokens);
    }

    public function test_api_is_rate_limited_per_user()
    {
        Sanctum::actingAs(User::factory()->create());

        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/user')->assertOk();
        }

        $this->getJson('/api/user')->assertTooManyRequests();
    }

    public function test_tokens_expire_after_30_days()
    {
        $this->assertSame(60 * 24 * 30, config('sanctum.expiration'));
    }
}
