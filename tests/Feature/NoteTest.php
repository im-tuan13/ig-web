<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_a_note(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('notes.store'), ['content' => 'A short note'])
            ->assertCreated()
            ->assertJsonPath('content', 'A short note');

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'content' => 'A short note',
        ]);
    }
}
