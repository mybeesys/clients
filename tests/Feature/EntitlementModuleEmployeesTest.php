<?php

use App\Services\Entitlements\EntitlementCatalog;

it('charges ten months for a full twelve-month annual term', function () {
    $catalog = app(EntitlementCatalog::class);

    expect($catalog->yearlyMonthsCharged())->toBe(10);
    expect($catalog->yearlyMonthsGifted())->toBe(2);
});

it('adds one employee seat per selected module on top of the platform quota', function () {
    $catalog = app(EntitlementCatalog::class);
    $base = (int) ($catalog->quota('employees')['included'] ?? 0);

    expect($catalog->moduleIncludedEmployees('cashier_pos'))->toBeGreaterThanOrEqual(1);
    expect($catalog->includedEmployees(['cashier_pos']))
        ->toBe($base + $catalog->moduleIncludedEmployees('cashier_pos'));
    expect($catalog->includedEmployees(['electronic_menu']))
        ->toBe($base + $catalog->moduleIncludedEmployees('electronic_menu'));

    expect($catalog->includedEmployees(['waiter_app']))->toBe(
        $base
        + $catalog->moduleIncludedEmployees('cashier_pos')
        + $catalog->moduleIncludedEmployees('waiter_app')
    );
});
