<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $company;
    public $pdfData;
    public $pdfFilename;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, $pdfData = null, $pdfFilename = null)
    {
        $this->invoice = $invoice->loadMissing('client'); // Ensure client is loaded
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
            subject: 'Invoice #' . $this->invoice->invoice_number . ' from ' . ($this->company->name ?? 'Our Company'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoices.sent',
            with: [
                'invoice' => $this->invoice,
                'companyName' => $this->company->name ?? 'Our Company',
                'clientName' => $this->invoice->client->contact_name ?? $this->invoice->client->company_name,
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
