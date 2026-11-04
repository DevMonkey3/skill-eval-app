<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_is_issued_for_valid_credentials()
    {
        $user = User::factory()->create();

        $this->postJson('/api/tokens', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test',
        ])
            ->assertCreated()
            ->assertJsonStructure(['token', 'token_type']);

        $this->assertCount(1, $user->tokens);
    }

    public function test_token_is_not_issued_for_a_wrong_password()
    {
        $user = User::factory()->create();

        $this->postJson('/api/tokens', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'test',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_issued_token_can_call_the_api_and_be_revoked()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', $user->email);

        $this->withToken($token)->deleteJson('/api/tokens/current')->assertNoContent();

        $this->assertCount(0, $user->tokens()->get());
    }
}
