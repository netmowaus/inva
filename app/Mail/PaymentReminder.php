<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $company;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice->loadMissing('client');
        $this->company = Company::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Payment Reminder: Invoice #' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoices.reminder',
            with: [
                'invoice' => $this->invoice,
                'companyName' => $this->company->name ?? 'Our Company',
                'clientName' => $this->invoice->client->contact_name ?? $this->invoice->client->company_name,
                'balanceDue' => $this->invoice->total_amount - $this->invoice->paid_amount,
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
        // Optionally, attach the invoice PDF with the reminder as well
        // To do this, you'd need to generate or fetch the PDF data here
        // For simplicity, not attaching PDF by default in reminder.
        return [];
    }
}
