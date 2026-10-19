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
        $embed = $this->wantsEmbed();

        if (! Schema::hasTable('subscription_handoff_tokens')) {
            return $this->fail('invalid', $embed);
        }

        $hash = hash('sha256', $token);

        $row = DB::table('subscription_handoff_tokens')
            ->where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $row) {
            return $this->fail('invalid', $embed);
        }

        $used = DB::table('subscription_handoff_tokens')
            ->where('id', $row->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        if ($used !== 1) {
            return $this->fail('invalid', $embed);
        }

        $user = User::query()->find($row->user_id);
        if (! $user) {
            return $this->fail('invalid', $embed);
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
                return $this->fail('forbidden', $embed);
            }
        }

        Auth::guard('web')->login($user, false);
        request()->session()->regenerate();

        $redirect = $row->redirect_to ?: '/subscribe';
        if (! str_starts_with($redirect, '/')) {
            $redirect = '/subscribe';
        }

        $embed = $embed || $this->redirectHasEmbed($redirect);

        session([
            'subscribe.company_id' => $row->company_id ? (int) $row->company_id : null,
            'subscribe.embed' => $embed,
        ]);

        if ($embed && ! $this->redirectHasEmbed($redirect)) {
            $redirect .= (str_contains($redirect, '?') ? '&' : '?').'embed=1';
        }

        return redirect()->to($redirect);
    }

    protected function wantsEmbed(): bool
    {
        $request = request();

        if ($request->boolean('embed')) {
            return true;
        }

        $dest = strtolower((string) $request->headers->get('Sec-Fetch-Dest', ''));

        return in_array($dest, ['iframe', 'nested'], true);
    }

    protected function redirectHasEmbed(string $redirect): bool
    {
        $query = parse_url($redirect, PHP_URL_QUERY);
        if (! is_string($query) || $query === '') {
            return false;
        }

        parse_str($query, $params);

        return filter_var($params['embed'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    protected function fail(string $reason, bool $embed): RedirectResponse
    {
        if ($embed) {
            return redirect()->route('subscribe.embed-error', ['reason' => $reason]);
        }

        $message = $reason === 'forbidden'
            ? __('main.subscribe.handoff_forbidden')
            : __('main.subscribe.handoff_invalid');

        return redirect()
            ->route('filament.admin.auth.login')
            ->withErrors(['email' => $message]);
    }
}
