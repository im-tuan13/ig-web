<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun utama
        $mainUser = User::create([
            'name' => 'Azfar Syabil',
            'username' => 'azfar',
            'email' => 'azfar@example.com',
            'password' => Hash::make('password'),
        ]);

        // User contoh
        $users = collect([
            User::create([
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'email' => 'budi@example.com',
                'password' => Hash::make('password'),
            ]),
            User::create([
                'name' => 'Andi Pratama',
                'username' => 'andi',
                'email' => 'andi@example.com',
                'password' => Hash::make('password'),
            ]),
            User::create([
                'name' => 'Siti Aisyah',
                'username' => 'siti',
                'email' => 'siti@example.com',
                'password' => Hash::make('password'),
            ]),
            User::create([
                'name' => 'Rizky Saputra',
                'username' => 'rizky',
                'email' => 'rizky@example.com',
                'password' => Hash::make('password'),
            ]),
        ]);

        // Tambah user random
        $users = $users->merge(User::factory(15)->create());

        // Follow semua user
        foreach ($users as $user) {
            $mainUser->following()->syncWithoutDetaching($user->id);
        }

        // Post untuk semua user
        foreach ($users->prepend($mainUser) as $user) {
            Post::factory()
                ->count(rand(2, 5))
                ->for($user)
                ->create();
        }
    }
}
