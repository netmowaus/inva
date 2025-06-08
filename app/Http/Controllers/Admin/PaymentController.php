<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    // Middleware (admin) will be applied via route group

    public function create(Invoice $invoice)
    {
        if ($invoice->status === 'paid' || $invoice->status === 'void') {
            return redirect()->route('admin.invoices.show', $invoice)->with('info', 'Invoice is already ' . $invoice->status . ' and cannot accept new payments.');
        }
        $remainingBalance = $invoice->total_amount - $invoice->paid_amount;
        return view('admin.payments.create', compact('invoice', 'remainingBalance'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid' || $invoice->status === 'void') {
            return redirect()->route('admin.invoices.show', $invoice)->with('error', 'Invoice is already ' . $invoice->status . ' and cannot accept new payments.');
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:' . ($invoice->total_amount - $invoice->paid_amount),
            'payment_method' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:255|unique:payments,transaction_id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment = $invoice->payments()->create($validated);
            $this->updateInvoicePaymentStatus($invoice);

            DB::commit();
            return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    public function edit(Payment $payment)
    {
        $invoice = $payment->invoice; // Get the associated invoice
        if ($invoice->status === 'void') {
             return redirect()->route('admin.invoices.show', $invoice)->with('info', 'Payments cannot be edited for a void invoice.');
        }
        // Max amount for edit should consider (total_invoice_amount - other_payments_for_this_invoice)
        $maxEditableAmount = $invoice->total_amount - ($invoice->paid_amount - $payment->amount);

        return view('admin.payments.edit', compact('payment', 'invoice', 'maxEditableAmount'));
    }

    public function update(Request $request, Payment $payment)
    {
        $invoice = $payment->invoice;
         if ($invoice->status === 'void') {
             return redirect()->route('admin.invoices.show', $invoice)->with('error', 'Payments cannot be edited for a void invoice.');
        }

        $maxEditableAmount = $invoice->total_amount - ($invoice->paid_amount - $payment->amount);

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:' . $maxEditableAmount,
            'payment_method' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:255|unique:payments,transaction_id,' . $payment->id,
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment->update($validated);
            $this->updateInvoicePaymentStatus($invoice);

            DB::commit();
            return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
         if ($invoice->status === 'void') {
             return redirect()->route('admin.invoices.show', $invoice)->with('error', 'Payments cannot be deleted for a void invoice.');
        }

        DB::beginTransaction();
        try {
            $payment->delete(); // Could be soft delete if enabled on Payment model
            $this->updateInvoicePaymentStatus($invoice);

            DB::commit();
            return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Payment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }

    /**
     * Updates the invoice's paid_amount and status based on its payments.
     */
    protected function updateInvoicePaymentStatus(Invoice $invoice)
    {
        $totalPaid = $invoice->payments()->sum('amount');
        $invoice->paid_amount = $totalPaid;

        if ($totalPaid >= $invoice->total_amount) {
            $invoice->status = 'paid';
        } elseif ($totalPaid > 0) {
            $invoice->status = 'partially_paid';
        } else {
            // If no payments, revert to 'sent' or 'overdue' based on due_date
            // For simplicity, reverting to 'sent'. Overdue logic can be a scheduled task or checked on display.
            $invoice->status = 'sent'; // Or 'draft' if it was never sent
            // A more complex logic might be needed here if it could revert from 'overdue'
            if ($invoice->due_date->isPast() && $invoice->status !== 'draft') {
                $invoice->status = 'overdue';
            } else if ($invoice->status !== 'draft') {
                 $invoice->status = 'sent';
            }
        }
        $invoice->save();
    }
}
