<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_profile_avatar_deletes_the_old_avatar_file(): void
    {
        $avatarDirectory = public_path('avatars');
        File::ensureDirectoryExists($avatarDirectory);

        $oldAvatar = 'avatars/old-avatar-'.uniqid().'.jpg';
        File::put(public_path($oldAvatar), 'old avatar');
        $user = User::factory()->create(['avatar' => $oldAvatar]);
        $newAvatar = null;

        try {
            $response = $this->actingAs($user)->post('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->create('new-avatar.jpg', 100, 'image/jpeg'),
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect('/profile');

            $newAvatar = $user->refresh()->avatar;

            $this->assertNotSame($oldAvatar, $newAvatar);
            $this->assertFileDoesNotExist(public_path($oldAvatar));
            $this->assertFileExists(public_path($newAvatar));
        } finally {
            if (File::exists(public_path($oldAvatar))) {
                File::delete(public_path($oldAvatar));
            }

            if ($newAvatar && File::exists(public_path($newAvatar))) {
                File::delete(public_path($newAvatar));
            }
        }
    }
}
