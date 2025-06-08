<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        @page {
            margin: 70px 50px;
        }
        header {
            position: fixed;
            top: -50px;
            left: 0px;
            right: 0px;
            height: 40px;
            text-align: center;
            border-bottom: 1px solid #ccc;
            font-size: 0.9em;
        }
        footer {
            position: fixed;
            bottom: -50px;
            left: 0px;
            right: 0px;
            height: 40px;
            text-align: center;
            border-top: 1px solid #ccc;
            font-size: 0.9em;
        }
        .container {
            width: 100%;
        }
        .header-section {
            margin-bottom: 30px;
        }
        .company-logo {
            max-width: 150px;
            max-height: 70px;
            float: left;
            margin-right: 20px;
        }
        .company-details {
            float: left;
        }
        .company-details h1 {
            margin: 0;
            font-size: 1.8em;
            color: #000;
        }
        .company-details p {
            margin: 2px 0;
        }
        .document-title {
            text-align: right;
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 20px;
            color: #555;
        }
        .address-section table {
            width: 100%;
            margin-bottom: 20px;
        }
        .address-section td {
            width: 50%;
            vertical-align: top;
        }
        .address-section h3 {
            font-size: 1.1em;
            margin-bottom: 5px;
            color: #d9534f; /* Different color for Invoice headers */
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .items-table td.number {
            text-align: right;
        }
        .totals-table {
            width: 50%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px;
            border: 1px solid #ccc;
        }
        .totals-table td.label {
            text-align: right;
            font-weight: bold;
        }
        .totals-table td.value {
            text-align: right;
        }
        .totals-table tr.grand-total td {
            font-weight: bold;
            font-size: 1.2em;
        }
        .totals-table tr.balance-due td {
            font-weight: bold;
            font-size: 1.3em;
            background-color: #f2f2f2;
            color: #d9534f;
        }
        .notes-section, .payment-terms-section {
            clear: both;
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #d9534f;
        }
        .notes-section h4, .payment-terms-section h4 { margin-top: 0; }
        .status-paid-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 5em;
            color: rgba(0, 128, 0, 0.2);
            border: 5px solid rgba(0, 128, 0, 0.2);
            padding: 10px 20px;
            border-radius: 10px;
            z-index: -1; /* Behind content */
            font-weight: bold;
            text-transform: uppercase;
        }
         .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <header>
        Invoice #{{ $invoice->invoice_number }} - {{ $company->name ?? 'Your Company' }}
    </header>

    <footer>
        Page <span class="page-number"></span>
        - Thank you for your business!
    </footer>

    @if($invoice->status == 'paid')
        <div class="status-paid-stamp">Paid</div>
    @endif

    <div class="container">
        <div class="header-section clearfix">
            @if(isset($company->logo_path) && $company->logo_path)
                 <div class="company-logo" style="text-align:center; border:1px solid #ccc; padding:10px; width:130px; height:50px;"> (Logo Placeholder) </div>
            @endif
            <div class="company-details">
                <h1>{{ $company->name ?? 'Your Company Name' }}</h1>
                <p>{{ nl2br(e($company->address ?? 'Your Company Address')) }}</p>
                <p>Phone: {{ $company->phone ?? 'Your Phone' }} | Email: {{ $company->email ?? 'Your Email' }}</p>
                 @if(isset($company->website) && $company->website)
                <p>Website: {{ $company->website }}</p>
                @endif
            </div>
        </div>

        <div class="document-title">INVOICE</div>

        <div class="address-section">
            <table>
                <tr>
                    <td>
                        <h3>Bill To:</h3>
                        <p><strong>{{ $invoice->client->company_name }}</strong></p>
                        <p>{{ $invoice->client->contact_name }}</p>
                        <p>{{ nl2br(e($invoice->client->address)) }}</p>
                        <p>Email: {{ $invoice->client->contact_email }}</p>
                        @if($invoice->client->phone)
                        <p>Phone: {{ $invoice->client->phone }}</p>
                        @endif
                    </td>
                    <td style="text-align: right; vertical-align: top;">
                        <h3>Details:</h3>
                        <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                        <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('F j, Y') }}</p>
                        <p><strong>Due Date:</strong> {{ $invoice->due_date->format('F j, Y') }}</p>
                        <p><strong>Status:</strong> <span style="text-transform: uppercase; font-weight:bold; color: {{ $invoice->status == 'paid' ? 'green' : ($invoice->status == 'overdue' ? 'red' : 'black') }}">{{ str_replace('_', ' ', $invoice->status) }}</span></p>
                        @if($invoice->quote_id)
                        <p><strong>Ref Quote #:</strong> {{ $invoice->quote->quote_number }}</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="number">{{ $item->quantity }}</td>
                    <td class="number">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="number">${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">${{ number_format($invoice->items->sum('total_price'), 2) }}</td>
            </tr>
            {{-- Add Tax, Discounts if applicable --}}
            <tr class="grand-total">
                <td class="label">Total Amount:</td>
                <td class="value">${{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Amount Paid:</td>
                <td class="value">${{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr class="balance-due">
                <td class="label">Balance Due:</td>
                <td class="value">${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td>
            </tr>
        </table>

        <div style="clear:both;"></div>

        @if($invoice->notes)
        <div class="notes-section">
            <h4>Notes:</h4>
            <p>{{ nl2br(e($invoice->notes)) }}</p>
        </div>
        @endif

        <div class="payment-terms-section">
            <h4>Payment Terms:</h4>
            <p>Please pay by {{ $invoice->due_date->format('F j, Y') }}.</p>
            {{-- Add bank details or other payment instructions here from Company model or config --}}
            <p>Thank you for your business!</p>
        </div>

    </div>
</body>
</html>
