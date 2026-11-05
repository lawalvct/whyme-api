<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Http\Resources\UserResource;
use App\Modules\Identity\Models\User;
use App\Modules\Platform\Exceptions\ApiException;
use App\Modules\Platform\Services\Audit;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Optimistic concurrency (OFF06, AT09): the client sends the version it edited.
 * If someone changed the profile since, the edit is refused with the current
 * record so the app can show "changed elsewhere — review".
 */
class UpdateProfile
{
    /**
     * @param  array{version: int, name?: string, locale?: string}  $data
     */
    public function handle(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $fresh = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($fresh->version !== (int) $data['version']) {
                throw new ApiException('conflict', 409, data: ['data' => UserResource::make($fresh->load('capabilities'))->resolve()]);
            }

            $changes = Arr::only($data, ['name', 'locale']);
            $old = Arr::only($fresh->getAttributes(), array_keys($changes));

            $fresh->fill($changes);
            if ($fresh->isDirty()) {
                $fresh->version++;
                $fresh->save();
                Audit::record('user.profile_updated', $fresh, $old, $changes, $fresh);
            }

            return $fresh;
        });
    }
}
