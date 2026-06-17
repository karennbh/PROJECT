<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('name')
                    ->label('Nama'),

                TextEntry::make('username')
                    ->label('Username'),

                TextEntry::make('user_group')
                    ->label('User Group')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-')),

                TextEntry::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
