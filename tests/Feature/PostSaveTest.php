<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_and_unsave_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create(['user_id' => User::factory()->create()->id, 'image' => 'posts/photo.jpg']);

        $this->actingAs($user)->postJson(route('posts.saves.store', $post))
            ->assertOk()->assertJson(['saved' => true]);
        $this->assertDatabaseHas('post_saves', ['user_id' => $user->id, 'post_id' => $post->id]);

        $this->actingAs($user)->deleteJson(route('posts.saves.destroy', $post))
            ->assertOk()->assertJson(['saved' => false]);
        $this->assertDatabaseMissing('post_saves', ['user_id' => $user->id, 'post_id' => $post->id]);
    }
}
