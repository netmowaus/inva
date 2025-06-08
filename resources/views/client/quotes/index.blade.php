<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quotes</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .alert-error { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
        .action-btn { display: inline-block; padding: 8px 12px; background: #5bc0de; color: #fff !important; text-decoration: none; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .no-quotes { text-align: center; color: #777; padding: 20px; }
        .pagination { margin-top: 20px; text-align: center; }
        .status-draft { background-color: #f0f0f0; color: #555; padding: 3px 6px; border-radius: 3px; }
        .status-sent { background-color: #d9edf7; color: #31708f; padding: 3px 6px; border-radius: 3px; }
        .status-accepted { background-color: #dff0d8; color: #3c763d; padding: 3px 6px; border-radius: 3px; }
        .status-rejected { background-color: #f2dede; color: #a94442; padding: 3px 6px; border-radius: 3px; }
        .status-invoiced { background-color: #fcf8e3; color: #8a6d3b; padding: 3px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Quotes</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($quotes->isEmpty())
            <p class="no-quotes">You currently have no quotes.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Quote Date</th>
                        <th>Expiry Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotes as $quote)
                        <tr>
                            <td>{{ $quote->quote_number }}</td>
                            <td>{{ $quote->quote_date->format('Y-m-d') }}</td>
                            <td>{{ $quote->expiry_date->format('Y-m-d') }}</td>
                            <td>${{ number_format($quote->total_amount, 2) }}</td>
                            <td><span class="status-{{ strtolower($quote->status) }}">{{ ucfirst($quote->status) }}</span></td>
                            <td>
                                <a href="{{ route('client.quotes.show', $quote->id) }}" class="action-btn">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
</body>
</html>
