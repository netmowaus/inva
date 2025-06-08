<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // Middleware (admin) will be applied via route group

    public function index()
    {
        $invoices = Invoice::with('client')->latest()->paginate(10);
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('company_name')->get();
        $quote = null;
        if ($request->has('quote_id')) {
            $quote = Quote::with('items', 'client')->findOrFail($request->quote_id);
            // Ensure quote is in a state that can be invoiced (e.g., accepted)
            if ($quote->status !== 'accepted' && $quote->status !== 'sent') { // Or other appropriate statuses
                 // Potentially redirect back with an error if quote not accepted
                return redirect()->route('admin.quotes.show', $quote)->with('error', 'This quote must be accepted or marked as sent before invoicing.');
            }
        }
        return view('admin.invoices.create', compact('clients', 'quote'));
    }

    // Specific method to trigger creation from a quote
    public function createFromQuote(Quote $quote)
    {
        if ($quote->status !== 'accepted' && $quote->status !== 'sent') {
            return redirect()->route('admin.quotes.show', $quote)->with('error', 'Quote must be accepted or marked as sent to be converted to an invoice.');
        }
        if ($quote->invoice()->exists()) {
            return redirect()->route('admin.invoices.show', $quote->invoice)->with('info', 'This quote has already been invoiced.');
        }
        // Pass quote_id to the standard create method
        return redirect()->route('admin.invoices.create', ['quote_id' => $quote->id]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'status' => 'required|in:draft,sent,paid,partially_paid,overdue,void',
            'notes' => 'nullable|string',
            'quote_id' => 'nullable|exists:quotes,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoiceData = $request->only(['client_id', 'invoice_date', 'due_date', 'status', 'notes', 'invoice_number', 'quote_id']);
            $invoiceData['user_id'] = Auth::id();
            $invoiceData['total_amount'] = 0; // Will be calculated
            $invoiceData['paid_amount'] = 0;

            $invoice = Invoice::create($invoiceData);

            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $invoice->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                ]);
                $totalAmount += $itemTotal;
            }

            $invoice->total_amount = $totalAmount;
            $invoice->save();

            // If created from a quote, update quote status
            if ($request->filled('quote_id')) {
                $quote = Quote::find($request->quote_id);
                if ($quote) {
                    $quote->status = 'invoiced';
                    $quote->save();
                }
            }

            DB::commit();
            return redirect()->route('admin.invoices.index')->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('client', 'user', 'items', 'quote', 'payments');
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $clients = Client::orderBy('company_name')->get();
        $invoice->load('items');
        // Determine if this invoice was created from a quote
        $sourceQuote = $invoice->quote_id ? Quote::find($invoice->quote_id) : null;
        return view('admin.invoices.edit', compact('invoice', 'clients', 'sourceQuote'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . $invoice->id,
            'status' => 'required|in:draft,sent,paid,partially_paid,overdue,void',
            'notes' => 'nullable|string',
            // quote_id is generally not changed after creation
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:invoice_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoiceData = $request->only(['client_id', 'invoice_date', 'due_date', 'status', 'notes', 'invoice_number']);
            $invoiceData['total_amount'] = 0; // Recalculate

            $totalAmount = 0;
            $existingItemIds = [];

            foreach ($validated['items'] as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $itemPayload = [
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                ];

                if (!empty($itemData['id'])) {
                    $item = InvoiceItem::find($itemData['id']);
                    if ($item && $item->invoice_id === $invoice->id) {
                        $item->update($itemPayload);
                        $existingItemIds[] = $item->id;
                    }
                } else {
                    $newItem = $invoice->items()->create($itemPayload);
                    $existingItemIds[] = $newItem->id;
                }
                $totalAmount += $itemTotal;
            }

            // Remove items not in submission
            $invoice->items()->whereNotIn('id', $existingItemIds)->delete();

            $invoiceData['total_amount'] = $totalAmount;
            // Note: paid_amount should be updated via Payments, not directly here unless specific adjustment.
            $invoice->update($invoiceData);

            DB::commit();
            return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        // Consider implications: payments, related quote status?
        // If an invoice is deleted, should the quote status revert? Generally no.
        // Soft delete is on Invoice model.
        if ($invoice->payments()->exists() && $invoice->status === 'paid') {
             return back()->with('error', 'Cannot delete an invoice that has payments and is marked paid. Consider voiding instead or removing payments first.');
        }

        DB::beginTransaction();
        try {
            // $invoice->items()->delete(); // Handled by cascade or not, depending on DB setup
            $invoice->delete(); // This is a soft delete
            DB::commit();
            return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    public function downloadPDF(Invoice $invoice)
    {
        $invoice->load('client', 'user', 'items', 'payments');
        $company = \App\Models\Company::first();

        $data = [
            'invoice' => $invoice,
            'company' => $company,
        ];

        try {
            $pdf = app('dompdf.wrapper')->loadView('pdf.invoice', $data);
            return $pdf->download('invoice_' . $invoice->invoice_number . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }

    public function sendEmail(Invoice $invoice)
    {
        if (!$invoice->client || !$invoice->client->contact_email) {
            return back()->with('error', 'Client email address not found for this invoice.');
        }

        $invoice->loadMissing('client', 'user', 'items', 'payments');
        $company = \App\Models\Company::first();

        try {
            // Generate PDF data
            $pdfData = app('dompdf.wrapper')->loadView('pdf.invoice', ['invoice' => $invoice, 'company' => $company])->output();
            $pdfFilename = 'invoice_' . $invoice->invoice_number . '.pdf';

            // Send email with PDF attachment
            \Illuminate\Support\Facades\Mail::to($invoice->client->contact_email)
                ->send(new \App\Mail\InvoiceSent($invoice, $pdfData, $pdfFilename));

            // Optionally update invoice status to 'sent' if it was 'draft'
            if ($invoice->status == 'draft') {
                $invoice->status = 'sent';
                $invoice->save();
            }

            return back()->with('success', 'Invoice #' . $invoice->invoice_number . ' emailed successfully to ' . $invoice->client->contact_email);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending invoice email for invoice ID ' . $invoice->id . ': ' . $e->getMessage());
            return back()->with('error', 'Failed to send invoice email: ' . $e->getMessage());
        }
    }
}
