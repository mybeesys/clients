<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncTenantOwnerPasswordService
{
    public function sync(User $user): void
    {
        $tenant = $user->tenant;

        if ($tenant === null) {
            return;
        }

        $lookupEmail = $user->wasChanged('email')
            ? ($user->getOriginal('email') ?? $user->email)
            : $user->email;

        if (! filled($lookupEmail) || $lookupEmail === 'admin@admin.com') {
            return;
        }

        $hashedPassword = $user->getAuthPassword();

        if (! filled($hashedPassword)) {
            return;
        }

        $updates = ['password' => $hashedPassword];

        if ($user->wasChanged('email') && filled($user->email)) {
            $updates['email'] = $user->email;
        }

        $tenant->run(function () use ($lookupEmail, $updates, $user, $tenant) {
            $updated = DB::table('emp_employees')
                ->where('email', $lookupEmail)
                ->update($updates);

            if ($updated === 0) {
                Log::warning('Tenant owner password sync: no emp_employees row matched', [
                    'tenant_id' => $tenant->id,
                    'email' => $lookupEmail,
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
