<?php

use App\Mail\RegistrationEmailCodeMail;
use App\Services\RegistrationEmailVerifier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

uses(Tests\TestCase::class);

beforeEach(function () {
    Cache::flush();
    Mail::fake();
});

function registrationVerifier(): RegistrationEmailVerifier
{
    return new class extends RegistrationEmailVerifier
    {
        protected function domainAcceptsMail(string $domain): bool
        {
            return $domain === 'inbox.test';
        }
    };
}

test('a registration code verifies only the inbox that received it', function () {
    $verifier = registrationVerifier();
    $email = 'owner@inbox.test';

    $verifier->send($email);

    $code = null;
    Mail::assertSent(RegistrationEmailCodeMail::class, function (RegistrationEmailCodeMail $mail) use (&$code, $email): bool {
        $code = $mail->code;

        return $mail->hasTo($email);
    });

    expect($verifier->isVerified($email))->toBeFalse()
        ->and($verifier->hasOpenChallenge($email))->toBeTrue();

    expect(fn () => $verifier->confirm($email, '000000'))->toThrow(ValidationException::class);

    $verifier->confirm($email, (string) $code);

    expect($verifier->isVerified($email))->toBeTrue()
        ->and($verifier->isVerified('other@inbox.test'))->toBeFalse()
        ->and($verifier->hasOpenChallenge($email))->toBeFalse();
});

test('temporary inboxes are rejected before a code is sent', function () {
    expect(fn () => registrationVerifier()->send('person@mailinator.com'))
        ->toThrow(ValidationException::class);

    Mail::assertNothingSent();
});

test('resend waits three minutes first and two more each time', function () {
    $verifier = registrationVerifier();

    expect($verifier->cooldownForSendCount(1))->toBe(180)
        ->and($verifier->cooldownForSendCount(2))->toBe(300)
        ->and($verifier->cooldownForSendCount(3))->toBe(420)
        ->and($verifier->retryAfterSeconds('owner@inbox.test'))->toBe(0);

    $verifier->send('owner@inbox.test');

    expect($verifier->retryAfterSeconds('owner@inbox.test'))->toBeGreaterThan(170)
        ->and($verifier->retryAfterSeconds('owner@inbox.test'))->toBeLessThanOrEqual(180);
});

test('a second code cannot be sent during the cooldown', function () {
    $verifier = registrationVerifier();
    $verifier->send('owner@inbox.test');

    expect(fn () => $verifier->send('owner@inbox.test'))->toThrow(ValidationException::class);
});

test('five wrong codes burn the challenge', function () {
    $verifier = registrationVerifier();
    $verifier->send('owner@inbox.test');

    for ($attempt = 0; $attempt < 4; $attempt++) {
        expect(fn () => $verifier->confirm('owner@inbox.test', '111111'))->toThrow(ValidationException::class);
    }

    expect(fn () => $verifier->confirm('owner@inbox.test', '111111'))->toThrow(ValidationException::class);
    expect($verifier->hasOpenChallenge('owner@inbox.test'))->toBeFalse();
});
