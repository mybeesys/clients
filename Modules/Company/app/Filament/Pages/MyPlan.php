<?php

namespace Modules\Company\Filament\Pages;

use App\Models\Company;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Modules\Administration\Models\Plan;
use Modules\Company\Models\Tenant;

class MyPlan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'company::filament.pages.my-plan';

    protected static ?string $navigationGroup = 'My Plan';

    public $subscription;

    public $plans;

    public $tenant;

    public function mount()
    {
        $this->plans =  Plan::on(env('DB_CONNECTION'))->active()->get();
        $this->tenant = Tenant::on(env('DB_CONNECTION'))->find(str_replace('_db', '', session('tenant')));

        if ($this->tenant && $this->tenant->subscriptions()->notCanceled()->where('expired_at', '>', now())->exists()) {
            $this->subscription = $this->tenant->subscriptions()->notCanceled()->where('expired_at', '>', now())->latest()->first();
        } else {
            \Log::error('There is no active subscription.');
        }
    }

    public function switchPlan($plan_id)
    {
        $new_plan =  Plan::on(env('DB_CONNECTION'))->find($plan_id);
        $company = Company::on(env('DB_CONNECTION'))->find($this->tenant->company_id);
        $subscription = $company->switchTo($new_plan);
        $subscription->update(['tenant_id' => $this->tenant->id, 'subdomain' => $this->tenant->domains()->first()->domain]);
        $company->subscribed = 1;
        $company->save();

        Notification::make()
            ->title('Plan switched successfully')
            ->success()
            ->send();

        return redirect()->back();
    }

    public function cancelSubscription()
    {

        $this->subscription->update(['canceled_at' => Carbon::now()]);
        Notification::make()
            ->title('Plan canceled successfully')
            ->success()
            ->send();

        return redirect()->back();
    }
}
