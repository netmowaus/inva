<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 10px; color: #333; }
        .invoice-header { border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .invoice-header p { margin: 5px 0; color: #555; }
        .invoice-header strong { color: #000; }
        .client-info h3, .items-table h3, .payment-info h3 { color: #337ab7; margin-bottom: 10px; }
        .items-table table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .items-table th { background-color: #f9f9f9; }
        .items-table td.number-cell { text-align: right; }
        .invoice-totals { margin-top: 20px; padding-top: 15px; border-top: 2px solid #337ab7; text-align: right; }
        .invoice-totals p { margin: 5px 0; font-size: 1.1em; }
        .invoice-totals strong { font-size: 1.3em; color: #337ab7; }
        .invoice-notes, .payment-info { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-left: 3px solid #337ab7; }
        .actions { margin-top: 30px; text-align: center; }
        .action-btn { display: inline-block; padding: 10px 18px; margin: 0 5px; background: #5bc0de; color: #fff !important; text-decoration: none; border-radius: 3px; }
        .btn-edit { background: #f0ad4e; }
        .btn-delete { background: #d9534f; }
        .btn-back { background: #777; }
        .btn-add-payment { background: #5cb85c; } /* Will be used in later payment subtask */
        .status-badge { padding: 5px 10px; border-radius: 4px; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 0.9em; }
        .status-draft { background-color: #777; }
        .status-sent { background-color: #31708f; }
        .status-paid { background-color: #5cb85c; }
        .status-partially_paid { background-color: #f0ad4e; color: #000 !important; }
        .status-overdue { background-color: #d9534f; }
        .status-void { background-color: #ccc; color: #000 !important; text-decoration: line-through;}
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .alert-info { color: #31708f; background-color: #d9edf7; border-color: #bce8f1; }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
         @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif


        <h1>Invoice #{{ $invoice->invoice_number }}
            <span class="status-badge status-{{ str_replace('_', '-', strtolower($invoice->status)) }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span>
        </h1>

        <div class="invoice-header">
            <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('F j, Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('F j, Y') }}</p>
            <p><strong>Created By:</strong> {{ $invoice->user->name ?? 'N/A' }}</p>
            @if($invoice->quote)
            <p><strong>Converted from Quote:</strong> <a href="{{ route('admin.quotes.show', $invoice->quote_id) }}">{{ $invoice->quote->quote_number }}</a></p>
            @endif
        </div>

        <div class="client-info">
            <h3>Client Details:</h3>
            <p><strong>Company:</strong> {{ $invoice->client->company_name }}</p>
            <p><strong>Contact:</strong> {{ $invoice->client->contact_name }} ({{ $invoice->client->contact_email }})</p>
            <p><strong>Address:</strong> {{ nl2br(e($invoice->client->address)) }}</p>
            @if($invoice->client->phone)
            <p><strong>Phone:</strong> {{ $invoice->client->phone }}</p>
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
            {{-- Add Tax, Discount if applicable --}}
            <p><strong>Total Amount: ${{ number_format($invoice->total_amount, 2) }}</strong></p>
            <p>Amount Paid: ${{ number_format($invoice->paid_amount, 2) }}</p>
            <p><strong>Balance Due: ${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</strong></p>
        </div>

        @if($invoice->notes)
        <div class="invoice-notes">
            <h3>Notes:</h3>
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
                <p>No payments recorded for this invoice yet.</p>
            @endif
            {{-- "Add Payment" button will be part of Payment subtask --}}
            {{-- <a href="#" class="action-btn btn-add-payment">Add Payment</a> --}}
        </div>


        <div class="actions">
            <a href="{{ route('admin.invoices.index') }}" class="action-btn btn-back">Back to List</a>
            @if($invoice->status !== 'paid' && $invoice->status !== 'void')
                <a href="{{ route('admin.invoices.edit', $invoice) }}" class="action-btn btn-edit">Edit Invoice</a>
            @endif
            {{-- TODO: Add "Send Email" button later --}}
            @if($invoice->status !== 'paid' && $invoice->status !== 'void') {{-- Generally, don't delete paid/voided invoices --}}
            <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-btn btn-delete">Delete Invoice</button>
            </form>
            @endif
        </div>
    </div>
</body>
</html>
