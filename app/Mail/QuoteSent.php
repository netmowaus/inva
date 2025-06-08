<?php

namespace App\Mail;

use App\Models\Quote;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App; // Corrected import

class QuoteSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $quote;
    public $company;
    public $pdfData;
    public $pdfFilename;

    /**
     * Create a new message instance.
     */
    public function __construct(Quote $quote, $pdfData = null, $pdfFilename = null)
    {
        $this->quote = $quote->loadMissing('client'); // Ensure client is loaded
        $this->company = Company::first(); // Assuming single company
        $this->pdfData = $pdfData;
        $this->pdfFilename = $pdfFilename;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Quote #' . $this->quote->quote_number . ' from ' . ($this->company->name ?? 'Our Company'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quotes.sent',
            with: [
                'quote' => $this->quote,
                'companyName' => $this->company->name ?? 'Our Company',
                'clientName' => $this->quote->client->contact_name ?? $this->quote->client->company_name,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfData && $this->pdfFilename) {
            return [
                Attachment::fromData(fn () => $this->pdfData, $this->pdfFilename)
                    ->withMime('application/pdf'),
            ];
        }
        return [];
    }
}
