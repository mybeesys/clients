<?php

namespace App\Jobs;

use App\Models\Company;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiredSubscribtion implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $users = Company::has('subscriptions')->with('subscriptions')->get();
        foreach ($users as $user) {
            foreach ($user->subscriptions as $subscription) {
                $end_date = $subscription->end_date;
                if ($end_date < now()) {
                    $subscription->delete();
                }
            }
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
