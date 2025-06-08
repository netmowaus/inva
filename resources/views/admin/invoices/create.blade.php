<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quote ? 'Create Invoice from Quote #'.$quote->quote_number : 'Create New Invoice' }}</title>
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
        .readonly-field { background-color: #eee; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $quote ? 'Create Invoice from Quote #'.$quote->quote_number : 'Create New Invoice' }}</h1>

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
        @if($quote)
            <div class="alert alert-info">
                Creating invoice based on Quote #{{ $quote->quote_number }}. Client and items are pre-filled.
            </div>
        @endif

        <form action="{{ route('admin.invoices.store') }}" method="POST" id="invoiceForm">
            @csrf
            @if($quote)
                <input type="hidden" name="quote_id" value="{{ $quote->id }}">
            @endif

            <div class="form-group">
                <label for="client_id">Client:</label>
                <select id="client_id" name="client_id" required {{ $quote ? 'disabled' : '' }}>
                    <option value="">Select a Client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $quote->client_id ?? '') == $client->id ? 'selected' : '' }}>
                            {{ $client->company_name }} ({{ $client->contact_name }})
                        </option>
                    @endforeach
                </select>
                @if($quote) {{-- Hidden input for disabled field --}}
                    <input type="hidden" name="client_id" value="{{ $quote->client_id }}">
                @endif
            </div>

            <div class="form-group">
                <label for="invoice_number">Invoice Number:</label>
                <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', 'INV-' . strtoupper(uniqid())) }}" required>
            </div>

            <div class="form-group">
                <label for="invoice_date">Invoice Date:</label>
                <input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $quote->expiry_date ?? date('Y-m-d', strtotime('+30 days'))) }}" required>
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="draft" {{ old('status', $quote ? 'sent' : 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ old('status', $quote ? 'sent' : 'draft') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partially_paid" {{ old('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="void" {{ old('status') == 'void' ? 'selected' : '' }}>Void</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional):</label>
                <textarea id="notes" name="notes">{{ old('notes', $quote->notes ?? '') }}</textarea>
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
                        @php $items = old('items', $quote ? $quote->items->toArray() : null); @endphp
                        @if(is_array($items) && count($items) > 0)
                            @foreach($items as $key => $item)
                            <tr class="item-row">
                                <td><input type="text" name="items[{{$key}}][description]" class="item-description" value="{{ $item['description'] ?? '' }}" required></td>
                                <td><input type="number" name="items[{{$key}}][quantity]" class="item-quantity" value="{{ $item['quantity'] ?? 1 }}" min="1" required></td>
                                <td><input type="number" name="items[{{$key}}][unit_price]" class="item-price" value="{{ isset($item['unit_price']) ? number_format(floatval($item['unit_price']), 2, '.', '') : '0.00' }}" step="0.01" min="0" required></td>
                                <td><span class="item-total">$0.00</span></td>
                                <td><button type="button" class="btn-remove-item">Remove</button></td>
                            </tr>
                            @endforeach
                        @else
                             <tr class="item-row">
                                <td><input type="text" name="items[0][description]" class="item-description" required></td>
                                <td><input type="number" name="items[0][quantity]" class="item-quantity" value="1" min="1" required></td>
                                <td><input type="number" name="items[0][unit_price]" class="item-price" value="0.00" step="0.01" min="0" required></td>
                                <td><span class="item-total">$0.00</span></td>
                                <td><button type="button" class="btn-remove-item">Remove</button></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <button type="button" id="addItemBtn" class="btn-add-item">Add Item</button>
                <p><small><strong>Note:</strong> JavaScript is used for item management and live totals.</small></p>
            </div>

            <div class="total-section">
                <strong>Grand Total: <span id="grandTotal">$0.00</span></strong>
            </div>

            <hr>
            <button type="submit" class="btn-submit">Save Invoice</button>
            <a href="{{ $quote ? route('admin.quotes.show', $quote->id) : route('admin.invoices.index') }}" class="btn-cancel">Cancel</a>
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
        calculateGrandTotal(); // Initial calculation
    });
</script>

</body>
</html>
