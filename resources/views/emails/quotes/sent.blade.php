<x-mail::message>
# Hello {{ $clientName }},

Here is quote **#{{ $quote->quote_number }}** from {{ $companyName }}.

**Quote Summary:**
- **Quote Number:** {{ $quote->quote_number }}
- **Quote Date:** {{ $quote->quote_date->format('F j, Y') }}
- **Expiry Date:** {{ $quote->expiry_date->format('F j, Y') }}
- **Total Amount:** ${{ number_format($quote->total_amount, 2) }}

Please review the attached PDF for full details.

You can also view the quote online using the button below:
<x-mail::button :url="route('client.quotes.show', $quote->id)">
View Quote Online
</x-mail::button>

If you have any questions, please don't hesitate to contact us.

Thanks,<br>
{{ $companyName ?? config('app.name') }}
</x-mail::message>
