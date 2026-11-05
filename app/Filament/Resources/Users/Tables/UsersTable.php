<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\Actions\UserStatusActions;
use App\Modules\Identity\Enums\UserStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (UserStatus $state): string => $state === UserStatus::Active ? 'success' : 'danger'),
                TextColumn::make('locale')
                    ->label('Language'),
                TextColumn::make('last_login_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        UserStatus::Active->value => 'Active',
                        UserStatus::Suspended->value => 'Suspended',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                ...UserStatusActions::make(),
            ]);
    }
}
