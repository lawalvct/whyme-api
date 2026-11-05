<?php

namespace App\Filament\Resources\Users\Actions;

use App\Filament\Resources\Users\UserResource;
use App\Modules\Identity\Actions\SetUserStatus;
use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Identity\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Suspend / reactivate, shared by the users table and the user page.
 */
class UserStatusActions
{
    /**
     * @return list<Action>
     */
    public static function make(): array
    {
        return [
            Action::make('suspend')
                ->label('Suspend')
                ->icon(Heroicon::OutlinedNoSymbol)
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('The user is signed out on every device and can no longer use the account until reactivated.')
                ->schema([Textarea::make('reason')->label('Reason (kept in the audit log)')->required()->maxLength(500)])
                ->visible(fn (User $record): bool => $record->status === UserStatus::Active)
                ->authorize(fn (): bool => UserResource::staff()?->can('users.manage') ?? false)
                ->action(function (User $record, array $data): void {
                    app(SetUserStatus::class)->handle($record, UserStatus::Suspended, UserResource::staff(), $data['reason']);
                    Notification::make()->title('Account suspended')->success()->send();
                }),

            Action::make('reactivate')
                ->label('Reactivate')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (User $record): bool => $record->status === UserStatus::Suspended)
                ->authorize(fn (): bool => UserResource::staff()?->can('users.manage') ?? false)
                ->action(function (User $record): void {
                    app(SetUserStatus::class)->handle($record, UserStatus::Active, UserResource::staff());
                    Notification::make()->title('Account reactivated')->success()->send();
                }),
        ];
    }
}
