<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quote #{{ $quote->quote_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif; /* Common PDF-safe fonts */
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        @page {
            margin: 70px 50px; /* top, right, bottom, left */
        }
        header {
            position: fixed;
            top: -50px; /* Adjust based on your @page margin-top */
            left: 0px;
            right: 0px;
            height: 40px;
            text-align: center;
            border-bottom: 1px solid #ccc;
            font-size: 0.9em;
        }
        footer {
            position: fixed;
            bottom: -50px; /* Adjust based on your @page margin-bottom */
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
            max-width: 150px; /* Adjust as needed */
            max-height: 70px; /* Adjust as needed */
            float: left;
            margin-right: 20px;
        }
        .company-details {
            float: left;
            /* If logo is present, adjust width or use a table for layout */
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
            color: #226d9c; /* A slightly different color for headers */
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
            background-color: #f2f2f2;
        }
        .notes-section {
            clear: both;
            margin-top: 30px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #226d9c;
        }
        .notes-section h4 { margin-top: 0; }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <header>
        Quote #{{ $quote->quote_number }} - {{ $company->name ?? 'Your Company' }}
    </header>

    <footer>
        Page <span class="page-number"></span> <!-- dompdf will fill this -->
        - Thank you for your business!
    </footer>

    <div class="container">
        <div class="header-section clearfix">
            @if(isset($company->logo_path) && $company->logo_path)
                {{-- This assumes logo_path is an absolute URL or you have public_path() access configured for dompdf --}}
                {{-- For local files, ensure dompdf has permissions and correct path. Example: public_path($company->logo_path) --}}
                {{-- <img src="{{ $company->logo_path }}" alt="{{ $company->name }} Logo" class="company-logo"> --}}
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

        <div class="document-title">QUOTE</div>

        <div class="address-section">
            <table>
                <tr>
                    <td>
                        <h3>To:</h3>
                        <p><strong>{{ $quote->client->company_name }}</strong></p>
                        <p>{{ $quote->client->contact_name }}</p>
                        <p>{{ nl2br(e($quote->client->address)) }}</p>
                        <p>Email: {{ $quote->client->contact_email }}</p>
                        @if($quote->client->phone)
                        <p>Phone: {{ $quote->client->phone }}</p>
                        @endif
                    </td>
                    <td style="text-align: right; vertical-align: top;">
                        <h3>Details:</h3>
                        <p><strong>Quote Number:</strong> {{ $quote->quote_number }}</p>
                        <p><strong>Quote Date:</strong> {{ $quote->quote_date->format('F j, Y') }}</p>
                        <p><strong>Expiry Date:</strong> {{ $quote->expiry_date->format('F j, Y') }}</p>
                        <p><strong>Status:</strong> <span style="text-transform: uppercase;">{{ $quote->status }}</span></p>
                        @if($quote->user)
                        <p><strong>Prepared By:</strong> {{ $quote->user->name }}</p>
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
                @foreach($quote->items as $index => $item)
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
                <td class="value">${{ number_format($quote->items->sum('total_price'), 2) }}</td>
            </tr>
            {{-- Add rows for Tax, Discounts if applicable --}}
            {{-- Example:
            <tr>
                <td class="label">Tax (10%):</td>
                <td class="value">${{ number_format($quote->total_amount * 0.1, 2) }}</td>
            </tr>
            --}}
            <tr class="grand-total">
                <td class="label">Grand Total:</td>
                <td class="value">${{ number_format($quote->total_amount, 2) }}</td>
            </tr>
        </table>

        <div style="clear:both;"></div>

        @if($quote->notes)
        <div class="notes-section">
            <h4>Notes:</h4>
            <p>{{ nl2br(e($quote->notes)) }}</p>
        </div>
        @endif

    </div>
</body>
</html>
