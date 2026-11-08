@php
    $email = (string) data_get($page->data ?? [], 'email', '');
    $verifier = app(\App\Services\RegistrationEmailVerifier::class);
    $verified = $verifier->isVerified($email);
    $pending = ! $verified && $verifier->hasOpenChallenge($email);
    $wait = $verifier->retryAfterSeconds($email);
@endphp

<div @class(['mb-email-verify', 'is-verified' => $verified, 'is-pending' => $pending])>
    <div class="mb-email-verify__copy">
        <div class="mb-email-verify__kicker">My Bee</div>
        <div class="mb-email-verify__title">
            {{ $verified ? __('main.wizard.email_verify_verified') : __('main.wizard.email_verify_title') }}
        </div>
        <p class="mb-email-verify__text">
            @if ($verified)
                <span dir="ltr">{{ $email }}</span>
            @elseif ($pending)
                {{ __('main.wizard.email_verify_sent', ['email' => $email]) }}
            @else
                {{ __('main.wizard.email_verify_body') }}
            @endif
        </p>
        @if ($page->accountEmailVerificationError)
            <p class="mb-email-verify__error">{{ $page->accountEmailVerificationError }}</p>
        @endif
    </div>

    @if ($pending)
        <div
            class="mb-otp"
            dir="ltr"
            x-data="{
                digits: ['', '', '', '', '', ''],
                busy: false,
                onInput(event, index) {
                    const value = (event.target.value || '').replace(/\D/g, '');
                    this.digits[index] = value.slice(-1);
                    event.target.value = this.digits[index];
                    if (this.digits[index] && index < 5) {
                        this.$refs['d' + (index + 1)].focus();
                    }
                    this.commit();
                },
                onKeydown(event, index) {
                    if (event.key === 'Backspace' && ! this.digits[index] && index > 0) {
                        this.digits[index - 1] = '';
                        this.$refs['d' + (index - 1)].focus();
                    }
                    if (event.key === 'ArrowLeft' && index > 0) {
                        this.$refs['d' + (index - 1)].focus();
                    }
                    if (event.key === 'ArrowRight' && index < 5) {
                        this.$refs['d' + (index + 1)].focus();
                    }
                },
                onPaste(event) {
                    const text = (event.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                    if (! text) return;
                    event.preventDefault();
                    this.digits = [0, 1, 2, 3, 4, 5].map((i) => text[i] || '');
                    this.$nextTick(() => this.$refs['d' + Math.min(text.length, 5)].focus());
                    this.commit();
                },
                commit() {
                    const code = this.digits.join('');
                    if (code.length !== 6 || this.busy) return;
                    this.busy = true;
                    this.$wire.confirmAccountVerificationCode(code).finally(() => { this.busy = false; });
                }
            }"
            x-init="$nextTick(() => $refs.d0 && $refs.d0.focus())"
        >
            @for ($i = 0; $i < 6; $i++)
                <input
                    x-ref="d{{ $i }}"
                    class="mb-otp__cell"
                    :class="{ 'is-filled': digits[{{ $i }}] !== '' }"
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                    aria-label="{{ __('main.wizard.email_verify_code_label') }} {{ $i + 1 }}"
                    @input="onInput($event, {{ $i }})"
                    @keydown="onKeydown($event, {{ $i }})"
                    @paste="onPaste($event)"
                >
            @endfor
        </div>
        <p class="mb-email-verify__hint">{{ __('main.wizard.email_verify_boxes_hint') }}</p>
    @endif

    @unless ($verified)
        <div
            class="mb-email-verify__actions"
            @if ($pending) x-data="{ left: {{ (int) $wait }}, id: null, start() { this.id = setInterval(() => { if (this.left > 0) this.left--; else clearInterval(this.id); }, 1000) }, label() { const m = String(Math.floor(this.left / 60)).padStart(2, '0'); const s = String(this.left % 60).padStart(2, '0'); return m + ':' + s; } }" x-init="start()" @endif
        >
            <button
                type="button"
                class="mb-email-verify__btn {{ $pending ? 'mb-email-verify__btn--quiet' : 'mb-email-verify__btn--primary' }}"
                wire:click="sendAccountVerificationCode"
                wire:loading.attr="disabled"
                wire:target="sendAccountVerificationCode"
                @if ($pending) :disabled="left > 0" @else @disabled($wait > 0) @endif
            >
                <span wire:loading.remove wire:target="sendAccountVerificationCode">
                    @if ($pending)
                        <span x-show="left > 0">{{ __('main.wizard.email_verify_timer') }} <span dir="ltr" x-text="label()"></span></span>
                        <span x-show="left === 0" x-cloak>{{ __('main.wizard.email_verify_resend') }}</span>
                    @else
                        {{ __('main.wizard.email_verify_send') }}
                    @endif
                </span>
                <span wire:loading wire:target="sendAccountVerificationCode">{{ __('main.wizard.email_verify_sending') }}</span>
            </button>
        </div>
    @endunless
</div>

<style>
    .mb-email-verify {
        display: grid;
        justify-items: start;
        gap: .85rem;
        width: 100%;
        padding: 1.05rem 1.1rem 1rem;
        border-radius: 20px;
        border: 1px solid rgba(184, 137, 18, .28);
        background:
            radial-gradient(90% 80% at 100% 0%, rgba(235, 184, 30, .16), transparent 58%),
            linear-gradient(180deg, #fffdf8 0%, #ffffff 72%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .9);
        text-align: start;
    }
    .mb-email-verify.is-verified {
        border-color: rgba(22, 163, 74, .28);
        background: linear-gradient(180deg, #f3fbf6, #ffffff);
    }
    .mb-email-verify__kicker {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .14em;
        color: #b88912;
    }
    .mb-email-verify.is-verified .mb-email-verify__kicker { color: #15803d; }
    .mb-email-verify__title {
        margin-top: .15rem;
        font-weight: 800;
        font-size: 1.02rem;
        color: #1a1a1a;
    }
    .mb-email-verify.is-verified .mb-email-verify__title { color: #166534; }
    .mb-email-verify__text {
        margin: .28rem 0 0;
        max-width: 38rem;
        color: #6b7280;
        line-height: 1.75;
        font-size: .9rem;
    }
    .mb-email-verify__error {
        margin: .45rem 0 0;
        color: #b91c1c;
        font-weight: 700;
        font-size: .86rem;
        line-height: 1.6;
    }
    .mb-email-verify__hint {
        margin: 0;
        color: #8a7340;
        font-size: .8rem;
        font-weight: 700;
    }
    .mb-otp {
        display: flex;
        justify-content: flex-start;
        gap: .5rem;
        width: 100%;
    }
    .mb-otp__cell {
        width: clamp(42px, 12vw, 54px);
        height: clamp(50px, 14vw, 60px);
        border-radius: 14px;
        border: 1px solid rgba(26, 26, 26, .12);
        background: #fff;
        text-align: center;
        font-size: 1.45rem;
        font-weight: 800;
        color: #1a1a1a;
        caret-color: #b88912;
        box-shadow: 0 8px 18px rgba(26, 26, 26, .04);
    }
    .mb-otp__cell:focus {
        outline: none;
        border-color: #ebb81e;
        box-shadow: 0 0 0 4px rgba(235, 184, 30, .22);
    }
    .mb-otp__cell.is-filled {
        border-color: rgba(184, 137, 18, .55);
        background: #fffaf0;
    }
    .mb-email-verify__actions { display: flex; }
    .mb-email-verify__btn {
        appearance: none;
        border: 0;
        cursor: pointer;
        border-radius: 12px;
        padding: .62rem 1rem;
        font: inherit;
        font-weight: 800;
        font-size: .9rem;
    }
    .mb-email-verify__btn:disabled { opacity: .6; cursor: not-allowed; }
    .mb-email-verify__btn--primary {
        background: #ebb81e;
        color: #111;
        box-shadow: 0 8px 20px rgba(235, 184, 30, .28);
    }
    .mb-email-verify__btn--quiet {
        background: transparent;
        color: #8a7340;
        padding-inline: 0;
    }
    [x-cloak] { display: none !important; }
</style>
