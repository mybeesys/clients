<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        thead {
            background-color: #f4f4f4;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total {
            font-weight: bold;
            text-align: right;
        }

        .total-value {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Subscription Report</h1>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Company Name</th>
                <th>Plan</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subscriptions as $index => $subscription)
                <tr>
                    <td>{{ $subscription->id }}</td>
                    <td>{{ $subscription->subscriber->name }}</td>
                    <td>{{ $subscription->plan->name }}</td>
                    <td>{{ $subscription->created_at->format('Y-m-d') }}</td>
                    <td>{{ $subscription->expired_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach

    </table>
    <div class="summary">
        <p><strong>Number of Companies:</strong> {{ $subscriptions->unique('company_id')->count() }}</p>
        <p><strong>Number of Subscription Payments:</strong>
            {{ $subscriptions->sum(function ($subscription) {
                return $subscription->payments ? $subscription->payments->count() : 0;
            }) }}
        </p>

    </div>

</body>

</html>
