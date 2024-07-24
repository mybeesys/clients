@extends('site.partials.layout')
@section('content')
    <div class="container mt-4">
        <div class="row">
            @foreach ($plans as $plan)
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h4 class="my-0 font-weight-normal">{{ $plan->name }}</h4>
                        </div>
                        <div class="card-body">
                            <h1 class="card-title pricing-card-title">${{ $plan->price }} <small class="text-muted">/
                                    mo</small></h1>
                            <ul class="list-unstyled mt-3 mb-4">
                                @foreach ($plan->features as $feature)
                                    <li>{{ $feature->name }}: {{ $feature->pivot->value }}</li>
                                @endforeach
                            </ul>

                            @if (auth()->guard('company')->user()->company->subscribed)
                                <button class="btn btn-lg btn-block btn-secondary" disabled>Already Subscribed</button>
                            @else
                                <form action="{{ route('site.company.subscribe') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="btn btn-lg btn-block btn-primary">Subscribe</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
