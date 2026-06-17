<?php

namespace App\Console\Commands;

use Filament\Commands\MakeUserCommand;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class MakeFilamentUserCommand extends MakeUserCommand
{
    protected function getOptions(): array
    {
        return [
            new InputOption(
                name: 'username',
                shortcut: null,
                mode: InputOption::VALUE_REQUIRED,
                description: 'A unique username',
            ),
            new InputOption(
                name: 'password',
                shortcut: null,
                mode: InputOption::VALUE_REQUIRED,
                description: 'The password for the user (min. 8 characters)',
            ),
            new InputOption(
                name: 'panel',
                shortcut: null,
                mode: InputOption::VALUE_REQUIRED,
                description: 'The panel to create the user in',
            ),
        ];
    }

    protected function getUserData(): array
    {
        $username = $this->options['username'] ?? text(
            label: 'Username',
            required: true,
            validate: fn (string $username): ?string => match (true) {
                ! preg_match('/^[a-z]+[a-z0-9]*$/', $username) => 'Username harus diawali huruf kecil, boleh ditambah angka, dan tidak boleh hanya angka atau mengandung spasi.',
                static::getUserModel()::query()->where('username', $username)->exists() => 'Username sudah digunakan.',
                default => null,
            },
        );

        return [
            'name' => $username,
            'username' => $username,
            'password' => Hash::make($this->options['password'] ?? password(
                label: 'Password',
                required: true,
            )),
        ];
    }
}
