<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RegistrationProfileDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_stores_avatar_and_bio_in_the_database(): void
    {
        $avatarDirectory = public_path('avatars');
        File::ensureDirectoryExists($avatarDirectory);

        $user = null;

        try {
            $response = $this->post('/register', [
                'name' => 'Avatar Bio User',
                'username' => 'avatarbiouser',
                'email' => 'avatar-bio@example.com',
                'bio' => 'A short profile biography.',
                'avatar' => UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg'),
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertRedirect(route('dashboard', absolute: false));

            $user = User::where('email', 'avatar-bio@example.com')->firstOrFail();

            $this->assertSame('A short profile biography.', $user->bio);
            $this->assertNotNull($user->avatar);
            $this->assertFileExists(public_path($user->avatar));
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'bio' => 'A short profile biography.',
                'avatar' => $user->avatar,
            ]);
        } finally {
            if ($user?->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }
        }
    }
}
