<?php

namespace App\Services;

use App\Mail\RegistrationEmailCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegistrationEmailVerifier
{
    public const VERIFIED_TTL_MINUTES = 30;

    public const FIRST_RESEND_SECONDS = 180;

    public const RESEND_STEP_SECONDS = 120;

    public const MAX_ATTEMPTS = 5;

    public function send(string $email): void
    {
        $email = $this->normalize($email);
        $this->assertAddress($email);

        if ($this->isVerified($email)) {
            return;
        }

        $this->assertSendAllowed($email);

        $sessionId = $this->sessionId();
        $key = $this->challengeKey($sessionId, $email);
        $previous = Cache::get($key);
        $sends = (is_array($previous) ? (int) ($previous['sends'] ?? 0) : 0) + 1;
        $code = (string) random_int(100000, 999999);
        $payload = [
            'code_hash' => $this->hashCode($code),
            'attempts' => 0,
            'sent_at' => time(),
            'sends' => $sends,
            'cooldown' => $this->cooldownForSendCount($sends),
        ];

        $this->rememberChallenge($key, $payload);

        try {
            Mail::to($email)->send(new RegistrationEmailCodeMail(
                code: $code,
                confirmUrl: $this->confirmUrl($sessionId, $email, (int) $payload['cooldown']),
                validFor: $this->minutesPhrase((int) ceil(((int) $payload['cooldown']) / 60)),
            ));
        } catch (Throwable $exception) {
            if (is_array($previous)) {
                $this->rememberChallenge($key, $previous);
            } else {
                Cache::forget($key);
            }

            report($exception);

            throw ValidationException::withMessages([
                'email' => __('main.wizard.email_verify_send_failed'),
            ]);
        }

        RateLimiter::hit($this->emailQuotaKey($email), 3600);
        RateLimiter::hit($this->ipQuotaKey(), 3600);
    }

    public function confirm(string $email, string $code): void
    {
        $email = $this->normalize($email);

        if ($this->isVerified($email)) {
            return;
        }

        $code = preg_replace('/\D+/', '', $code) ?? '';
        $sessionId = $this->sessionId(create: false);

        if ($sessionId === null || strlen($code) !== 6) {
            throw ValidationException::withMessages([
                'email_verification_code' => __('main.wizard.email_verify_invalid'),
            ]);
        }

        $key = $this->challengeKey($sessionId, $email);
        $payload = Cache::get($key);

        if (! is_array($payload) || ! isset($payload['code_hash'])) {
            throw ValidationException::withMessages([
                'email_verification_code' => __('main.wizard.email_verify_invalid'),
            ]);
        }

        $ttl = (int) ($payload['cooldown'] ?? self::FIRST_RESEND_SECONDS);

        if (time() - (int) ($payload['sent_at'] ?? 0) > $ttl) {
            throw ValidationException::withMessages([
                'email_verification_code' => __('main.wizard.email_verify_invalid'),
            ]);
        }

        $given = $this->hashCode($code);

        if (! hash_equals((string) $payload['code_hash'], $given)) {
            $payload['attempts'] = (int) ($payload['attempts'] ?? 0) + 1;

            if ($payload['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($key);

                throw ValidationException::withMessages([
                    'email_verification_code' => __('main.wizard.email_verify_too_many'),
                ]);
            }

            $this->rememberChallenge($key, $payload);

            throw ValidationException::withMessages([
                'email_verification_code' => __('main.wizard.email_verify_invalid'),
            ]);
        }

        $this->markVerified($sessionId, $email);
    }

    public function markVerified(string $sessionId, string $email): void
    {
        $email = $this->normalize($email);

        if ($email === '' || strlen($sessionId) < 20 || strlen($sessionId) > 80) {
            throw ValidationException::withMessages([
                'email' => __('main.wizard.email_verify_invalid'),
            ]);
        }

        Cache::put(
            $this->verifiedKey($sessionId, $email),
            true,
            now()->addMinutes(self::VERIFIED_TTL_MINUTES),
        );
        Cache::forget($this->challengeKey($sessionId, $email));
    }

    public function isVerified(string $email): bool
    {
        $email = $this->normalize($email);
        $sessionId = $this->sessionId(create: false);

        if ($email === '' || $sessionId === null) {
            return false;
        }

        return Cache::get($this->verifiedKey($sessionId, $email)) === true;
    }

    public function assertVerified(string $email): void
    {
        if ($this->isVerified($email)) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => __('main.wizard.email_verify_required'),
        ]);
    }

    public function hasOpenChallenge(string $email): bool
    {
        $email = $this->normalize($email);
        $sessionId = $this->sessionId(create: false);

        if ($email === '' || $sessionId === null) {
            return false;
        }

        return is_array(Cache::get($this->challengeKey($sessionId, $email)));
    }

    public function retryAfterSeconds(string $email): int
    {
        $email = $this->normalize($email);
        $sessionId = $this->sessionId(create: false);

        if ($email === '' || $sessionId === null) {
            return 0;
        }

        $payload = Cache::get($this->challengeKey($sessionId, $email));

        if (! is_array($payload)) {
            return 0;
        }

        $cooldown = (int) ($payload['cooldown'] ?? self::FIRST_RESEND_SECONDS);

        return max(0, $cooldown - (time() - (int) ($payload['sent_at'] ?? 0)));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function challengePayload(string $email): ?array
    {
        $email = $this->normalize($email);
        $sessionId = $this->sessionId(create: false);

        if ($email === '' || $sessionId === null) {
            return null;
        }

        $payload = Cache::get($this->challengeKey($sessionId, $email));

        return is_array($payload) ? $payload : null;
    }

    public function validityLabel(string $email): string
    {
        $seconds = self::FIRST_RESEND_SECONDS;
        $payload = $this->challengePayload($email);

        if (is_array($payload)) {
            $seconds = (int) ($payload['cooldown'] ?? $seconds);
        }

        return $this->minutesPhrase(max(1, (int) ceil($seconds / 60)));
    }

    public function minutesPhrase(int $minutes): string
    {
        $minutes = max(1, $minutes);

        if (app()->getLocale() === 'ar') {
            return match (true) {
                $minutes === 1 => 'دقيقة',
                $minutes === 2 => 'دقيقتين',
                $minutes <= 10 => $minutes.' دقائق',
                default => $minutes.' دقيقة',
            };
        }

        return $minutes === 1 ? '1 minute' : $minutes.' minutes';
    }

    public function cooldownForSendCount(int $sends): int
    {
        $sends = max(1, $sends);

        return self::FIRST_RESEND_SECONDS + (($sends - 1) * self::RESEND_STEP_SECONDS);
    }

    public function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }

    protected function assertAddress(string $email): void
    {
        Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email:rfc', 'max:255']],
            [
                'email.required' => __('main.wizard.email_verify_email_required'),
                'email.email' => __('main.wizard.email_verify_email_invalid'),
            ],
            ['email' => __('fields.email')],
        )->validate();

        $domain = Str::after($email, '@');

        if ($this->isDisposableDomain($domain)) {
            throw ValidationException::withMessages([
                'email' => __('main.wizard.email_verify_disposable'),
            ]);
        }

        if (! $this->domainAcceptsMail($domain)) {
            throw ValidationException::withMessages([
                'email' => __('main.wizard.email_verify_undeliverable'),
            ]);
        }

        Validator::make(
            ['email' => $email],
            ['email' => [Rule::unique('users', 'email')]],
            ['email.unique' => __('main.wizard.email_verify_email_taken')],
            ['email' => __('fields.email')],
        )->validate();
    }

    protected function assertSendAllowed(string $email): void
    {
        $wait = $this->retryAfterSeconds($email);

        if ($wait > 0) {
            throw ValidationException::withMessages([
                'email' => __('main.wizard.email_verify_wait', [
                    'duration' => $this->minutesPhrase(max(1, (int) ceil($wait / 60))),
                ]),
            ]);
        }

        if (RateLimiter::tooManyAttempts($this->emailQuotaKey($email), 5) || RateLimiter::tooManyAttempts($this->ipQuotaKey(), 8)) {
            throw ValidationException::withMessages([
                'email' => __('main.wizard.email_verify_limited'),
            ]);
        }
    }

    protected function domainAcceptsMail(string $domain): bool
    {
        $domain = rtrim(Str::lower($domain), '.');

        if ($domain === '' || ! function_exists('checkdnsrr')) {
            return false;
        }

        if (in_array($domain, ['localhost', 'invalid'], true) || Str::endsWith($domain, ['.test', '.local', '.invalid', '.example'])) {
            return false;
        }

        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA');
    }

    protected function isDisposableDomain(string $domain): bool
    {
        $domain = rtrim(Str::lower($domain), '.');

        return in_array($domain, [
            'mailinator.com', 'guerrillamail.com', 'guerrillamail.info', 'guerrillamail.biz',
            'guerrillamail.de', 'guerrillamail.net', 'guerrillamail.org', 'grr.la', 'sharklasers.com',
            'yopmail.com', 'yopmail.fr', 'tempmail.com', 'temp-mail.org', '10minutemail.com',
            'trashmail.com', 'trashmail.de', 'trashmail.net', 'getnada.com', 'dispostable.com',
            'maildrop.cc', 'fakeinbox.com', 'throwawaymail.com', 'mohmal.com', 'emailondeck.com',
            'mintemail.com', 'tempail.com', 'discard.email', 'mailnesia.com', 'mytemp.email',
            'moakt.com', 'inboxkitten.com', 'mailnull.com', 'jetable.org', 'dropmail.me',
            'tmpmail.org', 'tmpmail.net', 'spam4.me', 'bccto.me', 'chacuo.net',
        ], true);
    }

    protected function confirmUrl(string $sessionId, string $email, int $validForSeconds): string
    {
        $root = request()->getSchemeAndHttpHost();

        if (is_string($root) && $root !== '') {
            URL::forceRootUrl($root);
        }

        return URL::temporarySignedRoute('register.email.confirm', now()->addSeconds(max(1, $validForSeconds)), [
            'email' => $email,
            'sid' => $sessionId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function rememberChallenge(string $key, array $payload): void
    {
        $sentAt = (int) ($payload['sent_at'] ?? time());
        $cooldown = (int) ($payload['cooldown'] ?? self::FIRST_RESEND_SECONDS);
        $expiresAt = $sentAt + $cooldown;

        Cache::put($key, $payload, now()->addSeconds(max(1, $expiresAt - time())));
    }

    protected function sessionId(bool $create = true): ?string
    {
        $sessionId = session('registration.email_session');

        if (is_string($sessionId) && $sessionId !== '') {
            return $sessionId;
        }

        if (! $create) {
            return null;
        }

        $sessionId = Str::random(40);
        session(['registration.email_session' => $sessionId]);

        return $sessionId;
    }

    protected function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    protected function challengeKey(string $sessionId, string $email): string
    {
        return 'registration-email:challenge:'.$this->pairHash($sessionId, $email);
    }

    protected function verifiedKey(string $sessionId, string $email): string
    {
        return 'registration-email:verified:'.$this->pairHash($sessionId, $email);
    }

    protected function pairHash(string $sessionId, string $email): string
    {
        return hash_hmac('sha256', $sessionId.'|'.$email, (string) config('app.key'));
    }

    protected function emailQuotaKey(string $email): string
    {
        return 'registration-email:quota:'.hash('sha256', $email);
    }

    protected function ipQuotaKey(): string
    {
        return 'registration-email:ip:'.hash('sha256', (string) request()->ip());
    }
}
