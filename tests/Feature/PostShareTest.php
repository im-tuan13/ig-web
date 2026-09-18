<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_share_a_post_to_an_existing_conversation(): void
    {
        $sender = User::factory()->create();
        $conversation = Conversation::findOrCreateBetween($sender, User::factory()->create());
        $post = Post::create(['user_id' => User::factory()->create(['is_private' => false])->id, 'image' => 'posts/a.jpg', 'caption' => 'nice']);

        $this->actingAs($sender)->postJson(route('posts.share', $post), ['conversation_ids' => [$conversation->id]])->assertOk()->assertJson(['sent' => true, 'count' => 1]);
        $this->assertDatabaseHas('messages', ['conversation_id' => $conversation->id, 'user_id' => $sender->id, 'post_id' => $post->id]);
    }

    public function test_user_cannot_share_to_a_conversation_they_are_not_part_of(): void
    {
        $sender = User::factory()->create();
        $conversation = Conversation::findOrCreateBetween(User::factory()->create(), User::factory()->create());
        $post = Post::create(['user_id' => User::factory()->create(['is_private' => false])->id, 'image' => 'posts/a.jpg', 'caption' => 'nice']);

        $this->actingAs($sender)->postJson(route('posts.share', $post), ['conversation_ids' => [$conversation->id]])->assertOk()->assertJson(['sent' => true, 'count' => 0]);
        $this->assertDatabaseMissing('messages', ['conversation_id' => $conversation->id, 'post_id' => $post->id]);
    }

    public function test_user_cannot_share_a_private_post_they_cannot_view(): void
    {
        $sender = User::factory()->create();
        $conversation = Conversation::findOrCreateBetween($sender, User::factory()->create());
        $post = Post::create(['user_id' => User::factory()->create(['is_private' => true])->id, 'image' => 'posts/a.jpg', 'caption' => 'secret']);

        $this->actingAs($sender)->postJson(route('posts.share', $post), ['conversation_ids' => [$conversation->id]])->assertForbidden();
    }

    public function test_message_model_still_supports_story_id(): void
    {
        $this->assertContains('story_id', (new \App\Models\Message())->getFillable());
    }
}
