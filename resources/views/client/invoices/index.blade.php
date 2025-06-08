<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Invoices</title>
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
        .no-invoices { text-align: center; color: #777; padding: 20px; }
        .pagination { margin-top: 20px; text-align: center; }
        .status-badge { padding: 3px 6px; border-radius: 3px; color: white; font-size: 0.9em;}
        .status-draft { background-color: #777; }
        .status-sent { background-color: #31708f; }
        .status-paid { background-color: #5cb85c; }
        .status-partially_paid { background-color: #f0ad4e; color: #000;}
        .status-overdue { background-color: #d9534f; }
        .status-void { background-color: #ccc; color: #000; text-decoration: line-through;}
    </style>
</head>
<body>
    <div class="container">
        <h1>My Invoices</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($invoices->isEmpty())
            <p class="no-invoices">You currently have no invoices.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th>Total Amount</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
                            <td>${{ number_format($invoice->total_amount, 2) }}</td>
                            <td>${{ number_format($invoice->paid_amount, 2) }}</td>
                            <td><span class="status-badge status-{{ str_replace('_', '-', strtolower($invoice->status)) }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span></td>
                            <td>
                                <a href="{{ route('client.invoices.show', $invoice->id) }}" class="action-btn">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</body>
</html>
