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
                            <h1 class="card-title pricing-card-title">${{ $plan->price }}
                                <small class="text-muted">/ {{ $plan->periodicity_type }}</small>
                            </h1>
                            <ul class="list-unstyled mt-3 mb-4">
                                @foreach ($plan->features as $feature)
                                    <li>{{ $feature->name }}: {{ $feature->pivot->value }}</li>
                                @endforeach
                            </ul>

                            @if (auth()->guard('company')->user()->company->first()->subscribed)
                                <button class="btn btn-lg btn-block btn-secondary" disabled>Already Subscribed</button>
                            @else
                                <form action="{{ route('site.company.subscribe') }}" method="POST"
                                    id="payment-form-{{ $plan->id }}">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                                    <!-- Coupon Code Toggle Button -->

                                    <a class="btn btn-link" data-toggle="collapse"
                                        href="#couponCollapse-{{ $plan->id }}" role="button" aria-expanded="false"
                                        aria-controls="couponCollapse-{{ $plan->id }}">
                                        Have a coupon code?
                                    </a>

                                    <!-- Coupon Code Input (Initially Collapsed) -->
                                    <div class="collapse" id="couponCollapse-{{ $plan->id }}">
                                        <div class="form-group">
                                            <label for="coupon_code">Coupon Code</label>
                                            <input type="text" name="coupon_code" class="form-control"
                                                placeholder="Enter your coupon code">
                                        </div>
                                    </div>

                                    <div id="card-element-{{ $plan->id }}"></div>
                                    <button type="submit" class="btn btn-lg btn-block btn-primary mt-4" id="submit-button">
                                        Subscribe
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function openStripeWindow(planId, url) {
            const width = 500;
            const height = 600;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            window.open(url, 'Stripe Payment', `width=${width},height=${height},top=${top},left=${left}`);
        }
    </script>
@endsection
