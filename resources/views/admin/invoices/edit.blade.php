<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .form-group textarea { min-height: 80px; }
        .form-group .readonly-field { background-color: #eee; cursor: not-allowed; }
        .btn-submit { display: inline-block; padding: 12px 25px; background: #5cb85c; color: #fff; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; font-size: 16px; }
        .btn-cancel { display: inline-block; margin-left: 10px; color: #333; text-decoration: none; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-danger { background-color: #f2dede; border-color: #ebccd1; color: #a94442; }
        .alert-danger ul { list-style-type: none; padding-left: 0; margin-bottom:0; }
        .alert-info { color: #31708f; background-color: #d9edf7; border-color: #bce8f1; }
        .item-list table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .item-list th, .item-list td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .item-list th { background-color: #f9f9f9; }
        .item-list .btn-remove-item { background-color: #d9534f; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
        .btn-add-item { background-color: #337ab7; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-top:10px; }
        .total-section { margin-top:20px; text-align:right; font-size:1.2em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Invoice #{{ $invoice->invoice_number }}</h1>

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
        @if($sourceQuote)
            <div class="alert alert-info">
                This invoice was created from Quote <a href="{{route('admin.quotes.show', $sourceQuote->id)}}">#{{ $sourceQuote->quote_number }}</a>.
                Changing the client or core items might diverge from the original quote.
            </div>
        @endif

        <form action="{{ route('admin.invoices.update', $invoice) }}" method="POST" id="invoiceForm">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="client_id">Client:</label>
                <select id="client_id" name="client_id" required {{ $invoice->quote_id ? 'disabled' : '' }}>
                    <option value="">Select a Client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $invoice->client_id) == $client->id ? 'selected' : '' }}>
                            {{ $client->company_name }} ({{ $client->contact_name }})
                        </option>
                    @endforeach
                </select>
                 @if($invoice->quote_id) {{-- Hidden input for disabled field --}}
                    <input type="hidden" name="client_id" value="{{ $invoice->client_id }}">
                @endif
            </div>

            <div class="form-group">
                <label for="invoice_number">Invoice Number:</label>
                <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" required>
            </div>

            <div class="form-group">
                <label for="invoice_date">Invoice Date:</label>
                <input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="draft" {{ old('status', $invoice->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ old('status', $invoice->status) == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partially_paid" {{ old('status', $invoice->status) == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="overdue" {{ old('status', $invoice->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="void" {{ old('status', $invoice->status) == 'void' ? 'selected' : '' }}>Void</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional):</label>
                <textarea id="notes" name="notes">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            <hr>
            <h2>Invoice Items</h2>
            <div class="item-list">
                <table id="invoiceItemsTable">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $items = old('items', $invoice->items->toArray()); @endphp
                        @if(is_array($items))
                            @foreach($items as $key => $item)
                            <tr class="item-row">
                                <input type="hidden" name="items[{{$key}}][id]" value="{{ $item['id'] ?? '' }}">
                                <td><input type="text" name="items[{{$key}}][description]" class="item-description" value="{{ $item['description'] ?? '' }}" required></td>
                                <td><input type="number" name="items[{{$key}}][quantity]" class="item-quantity" value="{{ $item['quantity'] ?? 1 }}" min="1" required></td>
                                <td><input type="number" name="items[{{$key}}][unit_price]" class="item-price" value="{{ isset($item['unit_price']) ? number_format(floatval($item['unit_price']), 2, '.', '') : '0.00' }}" step="0.01" min="0" required></td>
                                <td><span class="item-total">$0.00</span></td>
                                <td><button type="button" class="btn-remove-item">Remove</button></td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <button type="button" id="addItemBtn" class="btn-add-item">Add Item</button>
                <p><small><strong>Note:</strong> JavaScript handles item management and live totals.</small></p>
            </div>

            <div class="total-section">
                <strong>Grand Total: <span id="grandTotal">$0.00</span></strong>
            </div>

            <hr>
            <button type="submit" class="btn-submit">Update Invoice</button>
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn-cancel">Cancel</a>
        </form>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemsTableBody = document.querySelector('#invoiceItemsTable tbody');
        const addItemBtn = document.getElementById('addItemBtn');
        let itemIndex = itemsTableBody.querySelectorAll('.item-row').length;

        function calculateRowTotal(row) {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = quantity * price;
            row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
            return total;
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            itemsTableBody.querySelectorAll('.item-row').forEach(row => {
                grandTotal += calculateRowTotal(row);
            });
            document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
        }

        addItemBtn.addEventListener('click', function () {
            const newRow = document.createElement('tr');
            newRow.classList.add('item-row');
            newRow.innerHTML = `
                <input type="hidden" name="items[${itemIndex}][id]" value="">
                <td><input type="text" name="items[${itemIndex}][description]" class="item-description" required></td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="item-quantity" value="1" min="1" required></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="item-price" value="0.00" step="0.01" min="0" required></td>
                <td><span class="item-total">$0.00</span></td>
                <td><button type="button" class="btn-remove-item">Remove</button></td>
            `;
            itemsTableBody.appendChild(newRow);
            itemIndex++;
            attachRowListeners(newRow);
            calculateGrandTotal();
        });

        function attachRowListeners(row) {
            row.querySelector('.btn-remove-item').addEventListener('click', function () {
                row.remove();
                calculateGrandTotal();
            });
            row.querySelectorAll('.item-quantity, .item-price').forEach(input => {
                input.addEventListener('input', function() {
                    calculateRowTotal(row);
                    calculateGrandTotal();
                });
            });
        }
        itemsTableBody.querySelectorAll('.item-row').forEach(row => {
            attachRowListeners(row);
        });
        calculateGrandTotal();
    });
</script>

</body>
</html>
