<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class SendPayrollSlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $pdfContent;

    public function __construct($data, $pdfContent)
    {
        $this->data = $data;
        $this->pdfContent = $pdfContent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Slip Gaji Resmi - PT MIRASA FOOD INDUSTRY (' . $this->data['bulanTahun'] . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payroll_slip', // Kita akan buat file view html email ini setelah ini
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'Slip_Gaji_' . $this->data['employee']->nama_karyawan . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}