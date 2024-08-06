<?php

namespace Modules\Company\Filament\Pages;

use Filament\Pages\Page;

class MyPlan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'company::filament.pages.my-plan';
    protected static ?string $navigationGroup = 'My Plan';

    public $subscription;

    public function mount()
    {
        $this->subscription = tenant()->subscriptions()->first();
    }
}
