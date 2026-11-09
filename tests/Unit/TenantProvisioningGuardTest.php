<?php

use App\Support\TenantAppAutoloader;
use App\Support\TenantDomainName;
use App\Support\TenantProvisioner;

uses(Tests\TestCase::class);

it('builds the company link from the central host', function () {
    config(['app.url' => 'https://mybeesystem.net']);

    expect(TenantDomainName::forTenantKey('bnhsyb2'))->toBe('bnhsyb2.mybeesystem.net');
});

it('loads tenant module classes from the module root during provisioning', function () {
    TenantAppAutoloader::register(base_path('../my-bee-company'));

    expect(class_exists(\Modules\Franchise\Support\FranchisePermissions::class))->toBeTrue();
});

it('rejects a tenant id that must never reach a shell command', function () {
    expect(TenantProvisioner::validTenantId('bnhsyb2'))->toBeTrue()
        ->and(TenantProvisioner::validTenantId('afranalslam'))->toBeTrue()
        ->and(TenantProvisioner::validTenantId('bnhsyb2;rm'))->toBeFalse()
        ->and(TenantProvisioner::validTenantId('../etc'))->toBeFalse()
        ->and(TenantProvisioner::validTenantId(''))->toBeFalse();
});
