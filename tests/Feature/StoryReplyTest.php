<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reply_to_another_users_story(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $story = Story::create([
            'user_id' => $owner->id,
            'image' => 'stories/reply.jpg',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($viewer)->post(route('stories.reply', $story), [
            'message' => 'Love this!',
        ]);

        $conversation = Conversation::findOrCreateBetween($viewer, $owner);

        $response->assertRedirect(route('messages.show', $conversation));
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $viewer->id,
            'message' => 'Love this!',
        ]);
    }

    public function test_user_cannot_reply_to_an_expired_story(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $story = Story::create([
            'user_id' => $owner->id,
            'image' => 'stories/expired.jpg',
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($viewer)
            ->post(route('stories.reply', $story), ['message' => 'Hello'])
            ->assertNotFound();

        $this->assertDatabaseCount('conversations', 0);
    }
}
