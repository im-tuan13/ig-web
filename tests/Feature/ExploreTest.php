<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_browse_paginated_latest_posts(): void
    {
        $viewer = User::factory()->create();
        $author = User::factory()->create();

        $posts = collect();

        foreach (range(1, 19) as $number) {
            $post = Post::create([
                'user_id' => $author->id,
                'image' => "posts/{$number}.jpg",
                'caption' => "Explore post {$number}",
            ]);

            // Pastikan urutan terbaru deterministik
            $post->forceFill([
                'created_at' => now()->addSeconds($number),
            ])->saveQuietly();

            $posts->push($post);
        }

        $latestPost = $posts->last();

        Model::preventLazyLoading();

        try {
            $response = $this->actingAs($viewer)
                ->get(route('explore.index'));
        } finally {
            Model::preventLazyLoading(false);
        }

        $response->assertOk()
            ->assertSee('Explore')
            ->assertSee($author->username)
            ->assertSee(route('posts.show', $latestPost), false)
            ->assertViewHas(
                'posts',
                fn ($paginator) => $paginator->total() === 19
                    && $paginator->perPage() === 18
                    && $paginator->getCollection()->contains('id', $latestPost->id)
                    && ! $paginator->getCollection()->contains('id', $posts->first()->id)
            );
    }
}
