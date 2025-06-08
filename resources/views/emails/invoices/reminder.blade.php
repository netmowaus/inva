<x-mail::message>
# Payment Reminder for Invoice #{{ $invoice->invoice_number }}

Hello {{ $clientName }},

This is a friendly reminder that invoice **#{{ $invoice->invoice_number }}** is due on **{{ $invoice->due_date->format('F j, Y') }}**.

**Invoice Summary:**
- **Invoice Number:** {{ $invoice->invoice_number }}
- **Invoice Date:** {{ $invoice->invoice_date->format('F j, Y') }}
- **Due Date:** {{ $invoice->due_date->format('F j, Y') }}
- **Original Amount:** ${{ number_format($invoice->total_amount, 2) }}
- **Amount Paid:** ${{ number_format($invoice->paid_amount, 2) }}
- **Balance Due:** <strong style="color: #d9534f;">${{ number_format($balanceDue, 2) }}</strong>

You can view and pay your invoice online using the button below:
<x-mail::button :url="route('client.invoices.show', $invoice->id)">
View Invoice
</x-mail::button>

If you have already made the payment, please disregard this email.
If you have any questions or need assistance, please contact us.

Thanks,<br>
{{ $companyName ?? config('app.name') }}
</x-mail::message>
