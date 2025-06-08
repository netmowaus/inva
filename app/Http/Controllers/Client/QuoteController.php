<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuoteController extends Controller
{
    // Middleware (auth) will be applied via route group.
    // We'll also need to ensure the user is a client, or that quotes are correctly scoped.

    protected function getClientQuotesQuery()
    {
        $user = Auth::user();
        // This assumes that a 'client' user has a related 'Client' model instance.
        // If your User model (role 'client') is directly the billable entity,
        // then quotes might be linked directly to user_id with role 'client'.
        // For this example, let's assume the Client model (company, contact_email)
        // has its contact_email matching the User's email for client role.
        // This is a common pattern but might need adjustment based on your exact User-Client linking.

        // Find the Client record associated with the authenticated User
        // This is a placeholder for your actual logic to connect User to Client records.
        // It could be $user->clientProfile (if you have such a relationship)
        // or matching by email if User.email is the Client.contact_email.
        $clientRecord = \App\Models\Client::where('contact_email', $user->email)->first();

        if (!$clientRecord) {
            return Quote::where('id', -1); // Return an empty query if no client record found for user
        }
        return Quote::where('client_id', $clientRecord->id);
    }


    public function index()
    {
        // Ensure only 'client' role users can access this, or that queries are properly scoped.
        if (Auth::user()->role !== 'client') {
            // Or redirect to an appropriate dashboard / error page
            abort(403, "Access denied. You must be logged in as a client.");
        }

        $quotes = $this->getClientQuotesQuery()
            ->with('client') // client here is for the relationship on Quote model
            ->latest()
            ->paginate(10);

        return view('client.quotes.index', compact('quotes'));
    }

    public function show(Request $request, $quoteId)
    {
        if (Auth::user()->role !== 'client') {
            abort(403, "Access denied.");
        }
        // Ensure the quote belongs to the authenticated client
        $quote = $this->getClientQuotesQuery()->with('items', 'client')->findOrFail($quoteId);
        return view('client.quotes.show', compact('quote'));
    }

    public function accept(Request $request, $quoteId)
    {
        if (Auth::user()->role !== 'client') {
            abort(403, "Access denied.");
        }
        $quote = $this->getClientQuotesQuery()->findOrFail($quoteId);

        if ($quote->status === 'sent' || $quote->status === 'draft') { // Or other acceptable statuses
            $quote->status = 'accepted';
            $quote->save();
            return redirect()->route('client.quotes.show', $quote)->with('success', 'Quote accepted successfully.');
        }
        return redirect()->route('client.quotes.show', $quote)->with('error', 'Quote cannot be accepted at this time.');
    }

    public function reject(Request $request, $quoteId)
    {
        if (Auth::user()->role !== 'client') {
            abort(403, "Access denied.");
        }
        $quote = $this->getClientQuotesQuery()->findOrFail($quoteId);

        if ($quote->status === 'sent' || $quote->status === 'draft') { // Or other acceptable statuses
            $quote->status = 'rejected';
            $quote->save();
            return redirect()->route('client.quotes.show', $quote)->with('success', 'Quote rejected successfully.');
        }
        return redirect()->route('client.quotes.show', $quote)->with('error', 'Quote cannot be rejected at this time.');
    }

    public function downloadPDF(Request $request, $quoteId)
    {
        if (Auth::user()->role !== 'client') {
            abort(403, "Access denied.");
        }
        $quote = $this->getClientQuotesQuery()->with('items', 'client', 'user')->findOrFail($quoteId);
        $company = \App\Models\Company::first(); // Assuming single company setup

        $data = [
            'quote' => $quote,
            'company' => $company, // The admin's company details
        ];

        try {
            $pdf = app('dompdf.wrapper')->loadView('pdf.quote', $data);
            return $pdf->download('quote_' . $quote->quote_number . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }
}
