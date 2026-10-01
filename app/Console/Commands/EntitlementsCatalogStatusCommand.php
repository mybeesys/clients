<?php

namespace App\Console\Commands;

use App\Models\EntitlementProduct;
use App\Services\Entitlements\EntitlementCatalog;
use Illuminate\Console\Command;

class EntitlementsCatalogStatusCommand extends Command
{
    protected $signature = 'entitlements:catalog-status';

    protected $description = 'Show entitlement catalog counts (DB + frontend payload)';

    public function handle(EntitlementCatalog $catalog): int
    {
        $byType = EntitlementProduct::query()
            ->selectRaw('type, COUNT(*) as aggregate, SUM(active) as active_count')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->type => [
                    'total' => (int) $row->aggregate,
                    'active' => (int) $row->active_count,
                ],
            ])
            ->all();

        $frontend = $catalog->toFrontend();

        $this->table(['Source', 'Count'], [
            ['db.platform', $byType['platform']['total'] ?? 0],
            ['db.module (active)', $byType['module']['active'] ?? 0],
            ['db.quota (active)', $byType['quota']['active'] ?? 0],
            ['config.modules', count(config('entitlements.modules', []))],
            ['config.quotas', count(config('entitlements.quotas', []))],
            ['config.groups', count(config('entitlements.groups', []))],
            ['frontend.modules', count($frontend['modules'] ?? [])],
            ['frontend.quotas', count($frontend['quotas'] ?? [])],
            ['frontend.groups', count($frontend['groups'] ?? [])],
            ['frontend.recommendations', count($frontend['recommendations'] ?? [])],
        ]);

        if (count($frontend['modules'] ?? []) === 0) {
            $this->error('Frontend modules are empty — subscribe builder will look blank.');

            return self::FAILURE;
        }

        $this->info('Catalog looks healthy.');

        return self::SUCCESS;
    }
}
