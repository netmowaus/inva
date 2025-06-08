<x-mail::message>
# Hello {{ $clientName }},

Please find attached your invoice **#{{ $invoice->invoice_number }}** from {{ $companyName }}.

**Invoice Summary:**
- **Invoice Number:** {{ $invoice->invoice_number }}
- **Invoice Date:** {{ $invoice->invoice_date->format('F j, Y') }}
- **Due Date:** {{ $invoice->due_date->format('F j, Y') }}
- **Total Amount Due:** ${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}
(Total: ${{number_format($invoice->total_amount,2)}}, Paid: ${{number_format($invoice->paid_amount,2)}})

The invoice PDF is attached to this email. You can also view your invoice online:
<x-mail::button :url="route('client.invoices.show', $invoice->id)">
View Invoice Online
</x-mail::button>

If you have any questions regarding this invoice, please contact us.

Thank you for your business!

Thanks,<br>
{{ $companyName ?? config('app.name') }}
</x-mail::message>
