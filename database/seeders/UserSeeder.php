<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => 'admin12345',
                'user_group' => 'admin',
            ],
            [
                'name' => 'Anggota',
                'username' => 'anggota',
                'password' => 'anggota12345',
                'user_group' => 'anggota',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'user_group' => $user['user_group'],
                ]
            );
        }
    }
}
