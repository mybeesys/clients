<?php

namespace Modules\Administration\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class AdministrationPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Administration';
    }

    public function getId(): string
    {
        return 'administration';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
