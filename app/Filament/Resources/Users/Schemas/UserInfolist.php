<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Modules\Identity\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('phone'),
                TextEntry::make('phone_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('locale')
                    ->label('Language'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('capabilities.capability')
                    ->label('Capabilities')
                    ->badge()
                    ->placeholder('Buyer only'),
                TextEntry::make('last_login_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Joined')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (User $record): bool => $record->trashed()),
            ]);
    }
}
