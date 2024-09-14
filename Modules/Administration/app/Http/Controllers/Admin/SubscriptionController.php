<?php

namespace Modules\Administration\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Administration\Models\PaymentSubscription;
use Modules\Administration\Models\Subscription;

class SubscriptionController extends Controller
{
    public function download()
    {
        $subscriptions = Subscription::all();
        $numberOfCompanies = Company::count();
        $numberOfSubscriptionPayments = PaymentSubscription::count();

        $pdf = PDF::loadView('administration::reports.subscription-report', [
            'subscriptions' => $subscriptions,
            'numberOfCompanies' => $numberOfCompanies,
            'numberOfSubscriptionPayments' => $numberOfSubscriptionPayments
        ]);

        return $pdf->download('subscription-report.pdf');
    }
}
