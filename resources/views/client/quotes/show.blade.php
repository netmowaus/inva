<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Details #{{ $quote->quote_number }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 10px; color: #333; }
        .quote-header { border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .quote-header p { margin: 5px 0; color: #555; }
        .quote-header strong { color: #000; }
        .company-info h3, .items-table h3 { color: #337ab7; margin-bottom: 10px; } /* Assuming your company info might be shown */
        .items-table table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .items-table th { background-color: #f9f9f9; }
        .items-table td.number-cell { text-align: right; }
        .quote-totals { margin-top: 20px; padding-top: 15px; border-top: 2px solid #337ab7; text-align: right; }
        .quote-totals p { margin: 5px 0; font-size: 1.1em; }
        .quote-totals strong { font-size: 1.3em; color: #337ab7; }
        .quote-notes { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-left: 3px solid #337ab7; }
        .actions { margin-top: 30px; text-align: center; padding-top: 20px; border-top: 1px solid #eee;}
        .action-btn { display: inline-block; padding: 10px 18px; margin: 5px; background: #5bc0de; color: #fff !important; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; font-size: 1em; }
        .btn-accept { background: #5cb85c; }
        .btn-reject { background: #d9534f; }
        .btn-back { background: #777; }
        .status-badge { padding: 5px 10px; border-radius: 4px; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 0.9em; }
        .status-draft { background-color: #777; }
        .status-sent { background-color: #31708f; }
        .status-accepted { background-color: #3c763d; }
        .status-rejected { background-color: #a94442; }
        .status-invoiced { background-color: #8a6d3b; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .alert-error { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <h1>Quote #{{ $quote->quote_number }}
            <span class="status-badge status-{{strtolower($quote->status)}}">{{ $quote->status }}</span>
        </h1>

        <div class="quote-header">
            <p><strong>Quote Date:</strong> {{ $quote->quote_date->format('F j, Y') }}</p>
            <p><strong>Expiry Date:</strong> {{ $quote->expiry_date->format('F j, Y') }}</p>
            {{-- <p><strong>From:</strong> [Your Company Name Here - This would come from Company model] </p> --}}
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
                    @foreach($quote->items as $index => $item)
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

        <div class="quote-totals">
            <p>Subtotal: ${{ number_format($quote->items->sum('total_price'), 2) }}</p>
            {{-- Add Tax, Discount if applicable --}}
            <p><strong>Grand Total: ${{ number_format($quote->total_amount, 2) }}</strong></p>
        </div>

        @if($quote->notes)
        <div class="quote-notes">
            <h3>Notes from Sender:</h3>
            <p>{{ nl2br(e($quote->notes)) }}</p>
        </div>
        @endif

        <div class="actions">
            <a href="{{ route('client.quotes.index') }}" class="action-btn btn-back">Back to My Quotes</a>
            @if($quote->status == 'sent' || $quote->status == 'draft') {{-- Or any other condition you set --}}
                <form action="{{ route('client.quotes.accept', $quote->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-btn btn-accept" onclick="return confirm('Are you sure you want to accept this quote?');">Accept Quote</button>
                </form>
                <form action="{{ route('client.quotes.reject', $quote->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-btn btn-reject" onclick="return confirm('Are you sure you want to reject this quote?');">Reject Quote</button>
                </form>
            @elseif($quote->status == 'accepted')
                <p>You have accepted this quote on {{ $quote->updated_at->format('F j, Y') }}.</p>
            @elseif($quote->status == 'rejected')
                <p>You have rejected this quote on {{ $quote->updated_at->format('F j, Y') }}.</p>
            @else
                 <p>This quote is currently {{ strtolower($quote->status) }} and no actions can be taken at this time.</p>
            @endif
        </div>
    </div>
</body>
</html>
