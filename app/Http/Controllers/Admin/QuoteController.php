<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // For database transactions

class QuoteController extends Controller
{
    // Middleware (admin) will be applied via route group

    public function index()
    {
        $quotes = Quote::with('client')->latest()->paginate(10);
        return view('admin.quotes.index', compact('quotes'));
    }

    public function create()
    {
        $clients = Client::orderBy('company_name')->get();
        return view('admin.quotes.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quote_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:quote_date',
            'quote_number' => 'required|string|unique:quotes,quote_number',
            'status' => 'required|in:draft,sent,accepted,rejected,invoiced',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $quoteData = $request->only(['client_id', 'quote_date', 'expiry_date', 'status', 'notes', 'quote_number']);
            $quoteData['user_id'] = Auth::id();
            $quoteData['total_amount'] = 0; // Will be calculated based on items

            $quote = Quote::create($quoteData);

            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $quote->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                ]);
                $totalAmount += $itemTotal;
            }

            $quote->total_amount = $totalAmount;
            $quote->save();

            DB::commit();
            return redirect()->route('admin.quotes.index')->with('success', 'Quote created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create quote: ' . $e->getMessage());
        }
    }

    public function show(Quote $quote)
    {
        $quote->load('client', 'user', 'items');
        return view('admin.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $clients = Client::orderBy('company_name')->get();
        $quote->load('items');
        return view('admin.quotes.edit', compact('quote', 'clients'));
    }

    public function update(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quote_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:quote_date',
            'quote_number' => 'required|string|unique:quotes,quote_number,' . $quote->id,
            'status' => 'required|in:draft,sent,accepted,rejected,invoiced',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:quote_items,id', // For existing items
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $quoteData = $request->only(['client_id', 'quote_date', 'expiry_date', 'status', 'notes', 'quote_number']);
            // user_id does not change on update typically
            $quoteData['total_amount'] = 0; // Will be recalculated

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
                    $item = QuoteItem::find($itemData['id']);
                    if ($item && $item->quote_id === $quote->id) {
                        $item->update($itemPayload);
                        $existingItemIds[] = $item->id;
                    }
                } else {
                    $newItem = $quote->items()->create($itemPayload);
                    $existingItemIds[] = $newItem->id;
                }
                $totalAmount += $itemTotal;
            }

            // Remove items that were not in the submission (were deleted by user)
            $quote->items()->whereNotIn('id', $existingItemIds)->delete();

            $quoteData['total_amount'] = $totalAmount;
            $quote->update($quoteData);

            DB::commit();
            return redirect()->route('admin.quotes.show', $quote)->with('success', 'Quote updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update quote: ' . $e->getMessage());
        }
    }

    public function destroy(Quote $quote)
    {
        try {
            // Related items will be deleted by database cascade if set up,
            // or manually: $quote->items()->delete();
            // Soft delete is on the Quote model.
            $quote->delete();
            return redirect()->route('admin.quotes.index')->with('success', 'Quote deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete quote: ' . $e->getMessage());
        }
    }

    public function downloadPDF(Quote $quote)
    {
        // Ensure all necessary relationships are loaded
        $quote->load('client', 'user', 'items');
        $company = \App\Models\Company::first(); // Assuming single company setup

        // Prepare data for the view
        $data = [
            'quote' => $quote,
            'company' => $company,
        ];

        try {
            $pdf = app('dompdf.wrapper')->loadView('pdf.quote', $data);
            return $pdf->download('quote_' . $quote->quote_number . '.pdf');
        } catch (\Exception $e) {
            // Log error or return a user-friendly message
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }

    public function sendEmail(Quote $quote)
    {
        if (!$quote->client || !$quote->client->contact_email) {
            return back()->with('error', 'Client email address not found for this quote.');
        }

        // Ensure relationships are loaded for both PDF and Mailable
        $quote->loadMissing('client', 'user', 'items');
        $company = \App\Models\Company::first();

        try {
            // Generate PDF data
            $pdfData = app('dompdf.wrapper')->loadView('pdf.quote', ['quote' => $quote, 'company' => $company])->output();
            $pdfFilename = 'quote_' . $quote->quote_number . '.pdf';

            // Send email with PDF attachment
            \Illuminate\Support\Facades\Mail::to($quote->client->contact_email)
                ->send(new \App\Mail\QuoteSent($quote, $pdfData, $pdfFilename));

            // Optionally update quote status to 'sent' if it was 'draft'
            if ($quote->status == 'draft') {
                $quote->status = 'sent';
                $quote->save();
            }

            return back()->with('success', 'Quote #' . $quote->quote_number . ' emailed successfully to ' . $quote->client->contact_email);

        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error sending quote email for quote ID ' . $quote->id . ': ' . $e->getMessage());
            return back()->with('error', 'Failed to send quote email: ' . $e->getMessage());
        }
    }
}
