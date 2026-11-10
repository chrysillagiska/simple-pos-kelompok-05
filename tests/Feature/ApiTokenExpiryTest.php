<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-kasir', ['products:read'], now()->addDay())
            ->plainTextToken;

        Carbon::setTestNow(now()->addDays(2));

        try {
            $this->withToken($token)
                ->getJson('/api/v1/products')
                ->assertUnauthorized(); 
        } finally {
            Carbon::setTestNow(); 
        }
    }

    public function test_valid_token_is_accepted(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-kasir', ['products:read'], now()->addDay())
            ->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/products')->assertOk();
    }
}