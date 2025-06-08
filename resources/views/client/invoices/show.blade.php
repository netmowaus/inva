<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Details #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 10px; color: #333; }
        .invoice-header { border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .invoice-header p { margin: 5px 0; color: #555; }
        .invoice-header strong { color: #000; }
        .company-info h3, .items-table h3, .payment-info h3 { color: #337ab7; margin-bottom: 10px; } /* Assuming your company info might be shown */
        .items-table table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .items-table th { background-color: #f9f9f9; }
        .items-table td.number-cell { text-align: right; }
        .invoice-totals { margin-top: 20px; padding-top: 15px; border-top: 2px solid #337ab7; text-align: right; }
        .invoice-totals p { margin: 5px 0; font-size: 1.1em; }
        .invoice-totals strong { font-size: 1.3em; color: #337ab7; }
        .invoice-notes, .payment-info { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-left: 3px solid #337ab7; }
        .actions { margin-top: 30px; text-align: center; padding-top: 20px; border-top: 1px solid #eee;}
        .action-btn { display: inline-block; padding: 10px 18px; margin: 5px; background: #5bc0de; color: #fff !important; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; font-size: 1em; }
        .btn-pay { background: #5cb85c; } /* For "Pay Now" button, if applicable later */
        .btn-back { background: #777; }
        .status-badge { padding: 5px 10px; border-radius: 4px; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 0.9em; }
        .status-draft { background-color: #777; }
        .status-sent { background-color: #31708f; }
        .status-paid { background-color: #5cb85c; }
        .status-partially_paid { background-color: #f0ad4e; color: #000 !important;}
        .status-overdue { background-color: #d9534f; }
        .status-void { background-color: #ccc; color: #000 !important; text-decoration: line-through;}
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <h1>Invoice #{{ $invoice->invoice_number }}
            <span class="status-badge status-{{ str_replace('_', '-', strtolower($invoice->status)) }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span>
        </h1>

        <div class="invoice-header">
            <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('F j, Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('F j, Y') }}</p>
            {{-- <p><strong>From:</strong> [Your Company Name Here - From Company model] </p> --}}
            @if($invoice->quote)
            <p><strong>Original Quote:</strong> {{ $invoice->quote->quote_number }}</p>
            @endif
        </div>

        <div class="items-table">
            <h3>Items:</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="number-cell">{{ $item->quantity }}</td>
                        <td class="number-cell">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="number-cell">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="invoice-totals">
            <p>Subtotal: ${{ number_format($invoice->items->sum('total_price'), 2) }}</p>
            <p><strong>Total Amount: ${{ number_format($invoice->total_amount, 2) }}</strong></p>
            <p>Amount Paid: ${{ number_format($invoice->paid_amount, 2) }}</p>
            <p><strong>Balance Due: ${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</strong></p>
        </div>

        @if($invoice->notes)
        <div class="invoice-notes">
            <h3>Notes from Sender:</h3>
            <p>{{ nl2br(e($invoice->notes)) }}</p>
        </div>
        @endif

        <div class="payment-info">
            <h3>Payment History:</h3>
            @if($invoice->payments->count() > 0)
                <ul>
                    @foreach($invoice->payments as $payment)
                        <li>
                            {{ $payment->payment_date->format('Y-m-d') }}:
                            ${{ number_format($payment->amount, 2) }}
                            ({{ $payment->payment_method ?? 'N/A' }})
                            @if($payment->transaction_id) TID: {{ $payment->transaction_id }} @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p>No payments have been recorded for this invoice.</p>
            @endif
        </div>

        <div class="actions">
            <a href="{{ route('client.invoices.index') }}" class="action-btn btn-back">Back to My Invoices</a>
            @if($invoice->status == 'sent' || $invoice->status == 'partially_paid' || $invoice->status == 'overdue')
                 {{-- "Pay Now" button will be part of Payment subtask --}}
                 {{-- <a href="#" class="action-btn btn-pay">Pay Now</a> --}}
            @endif
        </div>
    </div>
</body>
</html>
