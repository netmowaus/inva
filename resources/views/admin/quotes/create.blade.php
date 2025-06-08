<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Quote</title>
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
        <h1>Create New Quote</h1>

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

        <form action="{{ route('admin.quotes.store') }}" method="POST" id="quoteForm">
            @csrf

            <div class="form-group">
                <label for="client_id">Client:</label>
                <select id="client_id" name="client_id" required>
                    <option value="">Select a Client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->company_name }} ({{ $client->contact_name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="quote_number">Quote Number:</label>
                <input type="text" id="quote_number" name="quote_number" value="{{ old('quote_number', 'QN-' . strtoupper(uniqid())) }}" required>
            </div>

            <div class="form-group">
                <label for="quote_date">Quote Date:</label>
                <input type="date" id="quote_date" name="quote_date" value="{{ old('quote_date', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="expiry_date">Expiry Date:</label>
                <input type="date" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}" required>
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ old('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="accepted" {{ old('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="invoiced" {{ old('status') == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notes (Optional):</label>
                <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
            </div>

            <hr>
            <h2>Quote Items</h2>
            <div class="item-list">
                <table id="quoteItemsTable">
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
                        {{-- Item rows will be added here by JavaScript --}}
                        {{-- Simplified: Add one item row by default for non-JS scenarios or as a template --}}
                        @if(old('items'))
                            @foreach(old('items') as $key => $item)
                            <tr class="item-row">
                                <td><input type="text" name="items[{{$key}}][description]" class="item-description" value="{{ $item['description'] ?? '' }}" required></td>
                                <td><input type="number" name="items[{{$key}}][quantity]" class="item-quantity" value="{{ $item['quantity'] ?? 1 }}" min="1" required></td>
                                <td><input type="number" name="items[{{$key}}][unit_price]" class="item-price" value="{{ $item['unit_price'] ?? 0.00 }}" step="0.01" min="0" required></td>
                                <td><span class="item-total">$0.00</span></td>
                                <td><button type="button" class="btn-remove-item">Remove</button></td>
                            </tr>
                            @endforeach
                        @else
                            {{-- Add a default empty row if no old input --}}
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
                <p><small><strong>Note:</strong> In a full application, "Add Item" and "Remove" would use JavaScript to dynamically manage rows. For this simplified example, ensure at least one item is filled if JavaScript is disabled. The calculations for "Total" per item and the grand total would also be handled by JavaScript for real-time updates, with backend validation being the source of truth.</small></p>
            </div>

            <div class="total-section">
                <strong>Grand Total: <span id="grandTotal">$0.00</span></strong>
            </div>

            <hr>
            <button type="submit" class="btn-submit">Save Quote</button>
            <a href="{{ route('admin.quotes.index') }}" class="btn-cancel">Cancel</a>
        </form>
    </div>

<script>
    // Simplified JavaScript for adding items and calculating totals.
    // This is a basic example and would be more robust in a production app.
    document.addEventListener('DOMContentLoaded', function () {
        const itemsTableBody = document.querySelector('#quoteItemsTable tbody');
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

        // Initial calculation
        calculateGrandTotal();
    });
</script>

</body>
</html>
