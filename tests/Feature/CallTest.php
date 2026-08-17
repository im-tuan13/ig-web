<?php

namespace Tests\Feature;

use App\Models\Call;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallTest extends TestCase
{
    use RefreshDatabase;

    public function test_incoming_returns_a_structured_empty_response_when_no_call_exists(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('calls.incoming'))
            ->assertOk()
            ->assertExactJson(['has_call' => false, 'call' => null]);
    }

    public function test_incoming_marks_expired_call_as_missed_and_does_not_return_it(): void
    {
        $caller = User::factory()->create();
        $receiver = User::factory()->create();
        $call = Call::create([
            'caller_id' => $caller->id,
            'receiver_id' => $receiver->id,
            'type' => 'video',
            'status' => 'calling',
        ]);
        Call::whereKey($call)->update(['created_at' => now()->subSeconds(36)]);

        $this->actingAs($receiver)
            ->getJson(route('calls.incoming'))
            ->assertOk()
            ->assertExactJson(['has_call' => false, 'call' => null]);

        $this->assertDatabaseHas('calls', ['id' => $call->id, 'status' => 'missed']);
    }

    public function test_malformed_call_ids_return_validation_errors_before_model_lookup(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/calls/undefined/action', ['action' => 'end'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('call_id');

        $this->actingAs($user)
            ->postJson('/calls/undefined/signals', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('call_id');

        $this->actingAs($user)
            ->getJson('/calls/undefined/signals')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('call_id');
    }
}
