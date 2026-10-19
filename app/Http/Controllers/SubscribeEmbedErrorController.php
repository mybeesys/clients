<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscribeEmbedErrorController extends Controller
{
    public function __invoke(Request $request): View
    {
        $key = (string) $request->query('reason', 'invalid');
        $message = match ($key) {
            'forbidden' => __('main.subscribe.handoff_forbidden'),
            'unauthenticated' => __('main.subscribe.embed_error_unauthenticated'),
            default => __('main.subscribe.handoff_invalid'),
        };

        return view('subscribe.embed-error', compact('message'));
    }
}
