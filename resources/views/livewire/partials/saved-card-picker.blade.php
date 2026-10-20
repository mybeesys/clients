@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SavedPaymentCard> $savedCards */
    $savedCards = $savedCards ?? collect();
@endphp

@if ($savedCards->isNotEmpty())
    <div class="ms-card-pick">
        <div class="ms-card-pick__head">
            <strong>@lang('main.subscribe.saved_cards_pick')</strong>
        </div>
        <div class="ms-card-pick__grid">
            @foreach ($savedCards as $card)
                @php
                    $brand = strtoupper((string) ($card->brand ?: 'CARD'));
                    $isSelected = (int) $selected_card_id === (int) $card->id;
                @endphp
                <button
                    type="button"
                    class="ms-vault {{ $isSelected ? 'is-selected' : '' }} {{ $card->is_default ? 'is-default' : '' }}"
                    wire:click="selectSavedCard({{ (int) $card->id }})"
                    @disabled($saving)
                >
                    <span class="ms-vault__hex" aria-hidden="true"></span>
                    <span class="ms-vault__top">
                        <em class="ms-vault__brand">{{ $brand }}</em>
                        @if ($card->is_default)
                            <span class="ms-vault__star" title="{{ __('main.subscribe.default_card_badge') }}">★</span>
                        @endif
                    </span>
                    <span class="ms-vault__chip" aria-hidden="true"></span>
                    <span class="ms-vault__pan" dir="ltr">•••• •••• •••• {{ $card->last_four ?: '••••' }}</span>
                    <span class="ms-vault__meta">
                        <span>
                            <strong>@lang('fields.hyperpay_label_card_holder')</strong>
                            {{ $card->holder ?: $card->label() }}
                        </span>
                        <span>
                            <strong>@lang('fields.hyperpay_label_expiry')</strong>
                            {{ $card->expiryLabel() }}
                        </span>
                    </span>
                    @unless ($card->is_default)
                        <span
                            class="ms-vault__default-btn"
                            wire:click.stop="setDefaultSavedCard({{ (int) $card->id }})"
                            role="button"
                        >@lang('main.subscribe.set_default_card')</span>
                    @endunless
                </button>
            @endforeach

            <button
                type="button"
                class="ms-vault ms-vault--new {{ (int) $selected_card_id === 0 ? 'is-selected' : '' }}"
                wire:click="selectSavedCard(0)"
                @disabled($saving)
            >
                <span class="ms-vault__plus">+</span>
                <span class="ms-vault__pan">@lang('main.subscribe.pay_with_new')</span>
            </button>
        </div>
    </div>
@endif
