<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payment for Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 700px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .form-group textarea { min-height: 80px; }
        .btn-submit { display: inline-block; padding: 12px 25px; background: #5cb85c; color: #fff; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; font-size: 16px; }
        .btn-cancel { display: inline-block; margin-left: 10px; color: #333; text-decoration: none; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-danger { background-color: #f2dede; border-color: #ebccd1; color: #a94442; }
        .alert-danger ul { list-style-type: none; padding-left: 0; margin-bottom:0; }
        .invoice-summary { padding: 15px; background-color: #eef; border-left: 3px solid #337ab7; margin-bottom: 20px; }
        .invoice-summary p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Payment for Invoice #{{ $invoice->invoice_number }}</h1>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops! Something went wrong.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="invoice-summary">
            <p><strong>Client:</strong> {{ $invoice->client->company_name }}</p>
            <p><strong>Invoice Total:</strong> ${{ number_format($invoice->total_amount, 2) }}</p>
            <p><strong>Currently Paid (Excluding this payment):</strong> ${{ number_format($invoice->paid_amount - $payment->amount, 2) }}</p>
            <p><strong>Max Editable Amount for this payment:</strong> ${{ number_format($maxEditableAmount, 2) }}</p>
        </div>

        <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="payment_date">Payment Date:</label>
                <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" value="{{ old('amount', number_format($payment->amount, 2, '.', '')) }}" step="0.01" min="0.01" max="{{number_format($maxEditableAmount, 2, '.', '')}}" required>
                <small>Cannot exceed invoice total minus other payments.</small>
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method (Optional):</label>
                <input type="text" id="payment_method" name="payment_method" value="{{ old('payment_method', $payment->payment_method) }}" placeholder="e.g., Bank Transfer, Credit Card, Cash">
            </div>

            <div class="form-group">
                <label for="transaction_id">Transaction ID (Optional):</label>
                <input type="text" id="transaction_id" name="transaction_id" value="{{ old('transaction_id', $payment->transaction_id) }}">
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional):</label>
                <textarea id="notes" name="notes">{{ old('notes', $payment->notes) }}</textarea>
            </div>

            <button type="submit" class="btn-submit">Update Payment</button>
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>
