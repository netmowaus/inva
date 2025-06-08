<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    // Middleware (auth) will be applied via route group.

    protected function getClientInvoicesQuery()
    {
        $user = Auth::user();
        // This logic should mirror how clients are associated with quotes for consistency.
        // Assuming User.email matches Client.contact_email
        $clientRecord = \App\Models\Client::where('contact_email', $user->email)->first();

        if (!$clientRecord) {
            return Invoice::where('id', -1); // Return an empty query if no client record found
        }
        return Invoice::where('client_id', $clientRecord->id);
    }

    public function index()
    {
        if (Auth::user()->role !== 'client') {
            abort(403, "Access denied.");
        }

        $invoices = $this->getClientInvoicesQuery()
            ->with('client') // Relationship on Invoice model
            ->latest()
            ->paginate(10);

        return view('client.invoices.index', compact('invoices'));
    }

    public function show(Request $request, $invoiceId)
    {
        if (Auth::user()->role !== 'client') {
            abort(403, "Access denied.");
        }

        $invoice = $this->getClientInvoicesQuery()
            ->with('items', 'client', 'payments') // Load necessary relations
            ->findOrFail($invoiceId);

        return view('client.invoices.show', compact('invoice'));
    }

    public function downloadPDF(Request $request, $invoiceId)
    {
        if (Auth::user()->role !== 'client') {
            abort(403, "Access denied.");
        }

        $invoice = $this->getClientInvoicesQuery()
            ->with('items', 'client', 'user', 'payments')
            ->findOrFail($invoiceId);

        $company = \App\Models\Company::first(); // Assuming single company setup

        $data = [
            'invoice' => $invoice,
            'company' => $company, // The admin's company details
        ];

        try {
            $pdf = app('dompdf.wrapper')->loadView('pdf.invoice', $data);
            return $pdf->download('invoice_' . $invoice->invoice_number . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }
}
