<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_post_form_posts_to_the_store_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/posts/create');

        $response->assertStatus(200);
        $response->assertSee('enctype="multipart/form-data"', false);
        $response->assertSee('action="'.route('posts.store').'"', false);
        $response->assertDontSee('action="../posts"', false);
    }

    public function test_user_can_create_a_post_with_an_uploaded_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        // `create` keeps this test independent from PHP's optional GD extension.
        $file = UploadedFile::fake()->create('post.jpg', 1024, 'image/jpeg');

        $response = $this->actingAs($user)->post('/posts', [
            'caption' => 'Hello world',
            'image' => $file,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Your post has been shared.');
        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'caption' => 'Hello world',
        ]);

        $post = Post::latest()->first();
        $this->assertNotNull($post);
        $this->assertStringStartsWith('posts/', $post->image);
        Storage::disk('public')->assertExists($post->image);
    }

    public function test_user_can_create_a_post_with_multiple_uploaded_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $files = [
            UploadedFile::fake()->create('post-1.jpg', 1024, 'image/jpeg'),
            UploadedFile::fake()->create('post-2.jpg', 1024, 'image/jpeg'),
        ];

        $response = $this->actingAs($user)->post('/posts', [
            'caption' => 'Multi image post',
            'images' => $files,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Your post has been shared.');

        $post = Post::latest()->first();
        $this->assertNotNull($post);
        $this->assertStringStartsWith('posts/', $post->image);
        $this->assertEquals(2, $post->images()->count());
        $this->assertEquals([0, 1], $post->images()->pluck('position')->all());
    }

    public function test_deleting_a_post_removes_its_uploaded_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('post.jpg', 1024, 'image/jpeg');

        $this->actingAs($user)->post('/posts', [
            'caption' => 'Delete me',
            'image' => $file,
        ]);

        $post = Post::latest()->first();
        $this->assertNotNull($post);
        $this->assertNotEmpty($post->image);
        Storage::disk('public')->assertExists($post->image);

        $post->delete();

        Storage::disk('public')->assertMissing($post->image);
        $this->assertDatabaseMissing('post_images', ['post_id' => $post->id]);
    }
}
