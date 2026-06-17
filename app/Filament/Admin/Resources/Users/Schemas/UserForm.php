<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes([
                'novalidate' => true,
            ])
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->rule('required')
                    ->rule('min:3')
                    ->rule('max:50')
                    ->rule('regex:/^[A-Za-z\s]+$/')
                    ->unique(table: User::class, column: 'name', ignoreRecord: true)
                    ->validationMessages([
                        'required' => 'Nama wajib diisi.',
                        'min' => 'Nama minimal 3 karakter.',
                        'max' => 'Nama maksimal 50 karakter.',
                        'regex' => 'Nama hanya boleh berisi huruf.',
                        'unique' => 'Nama sudah terdaftar.',
                    ]),

                TextInput::make('username')
                    ->label('Username')
                    ->rule('required')
                    ->rule('regex:/^[a-z]+[a-z0-9]*$/')
                    ->rule('min:5')
                    ->rule('max:50')
                    ->unique(table: User::class, column: 'username', ignoreRecord: true)
                    ->default(null)
                    ->autocomplete('off')
                    ->extraInputAttributes([
                        'autocomplete' => 'off',
                    ])
                    ->validationMessages([
                        'required' => 'Username wajib diisi.',
                        'regex' => 'Username menggunakan huruf kecil semua, boleh ditambah angka, dan tidak boleh hanya angka atau mengandung spasi.',
                        'min' => 'Username minimal 5 karakter.',
                        'max' => 'Username maksimal 50 karakter.',
                        'unique' => 'Username sudah terdaftar.',
                    ]),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->rule('required')
                    ->rule('min:6')
                    ->rule('max:12')
                    ->default(null)
                    ->autocomplete('new-password')
                    ->extraInputAttributes([
                        'autocomplete' => 'new-password',
                    ])
                    ->helperText('Password hanya bisa dilihat saat diinput. Setelah disimpan, sistem mengenkripsi password sehingga tidak bisa ditampilkan kembali.')
                    ->validationMessages([
                        'required' => 'Password wajib diisi.',
                        'min' => 'Password tidak boleh kurang dari 6 karakter.',
                        'max' => 'Password maksimal 12 karakter.',
                    ])
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

                Select::make('user_group')
                    ->label('User Group')
                    ->options([
                        'admin' => 'Admin',
                        'anggota' => 'Anggota',
                    ])
                    ->required()
                    ->default('admin')
                    ->validationMessages([
                        'required' => 'User group wajib dipilih.',
                    ]),
            ]);
    }
}
