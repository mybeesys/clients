<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubscribeHandoffController extends Controller
{
    public function __invoke(string $token): RedirectResponse
    {
        if (! Schema::hasTable('subscription_handoff_tokens')) {
            abort(503, 'Subscription handoff is not configured yet.');
        }

        $hash = hash('sha256', $token);

        $row = DB::table('subscription_handoff_tokens')
            ->where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $row) {
            return redirect()
                ->route('filament.admin.auth.login')
                ->withErrors(['email' => __('main.subscribe.handoff_invalid')]);
        }

        $used = DB::table('subscription_handoff_tokens')
            ->where('id', $row->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        if ($used !== 1) {
            return redirect()
                ->route('filament.admin.auth.login')
                ->withErrors(['email' => __('main.subscribe.handoff_invalid')]);
        }

        $user = User::query()->find($row->user_id);
        if (! $user) {
            return redirect()
                ->route('filament.admin.auth.login')
                ->withErrors(['email' => __('main.subscribe.handoff_invalid')]);
        }

        if ($row->company_id) {
            $allowed = Company::query()
                ->where('id', $row->company_id)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhereHas('members', fn ($members) => $members->where('users.id', $user->id));
                })
                ->exists();

            if (! $allowed) {
                return redirect()
                    ->route('filament.admin.auth.login')
                    ->withErrors(['email' => __('main.subscribe.handoff_forbidden')]);
            }
        }

        Auth::guard('web')->login($user, false);
        request()->session()->regenerate();

        $redirect = $row->redirect_to ?: '/subscribe';
        if (! str_starts_with($redirect, '/')) {
            $redirect = '/subscribe';
        }

        return redirect()->to($redirect);
    }
}
