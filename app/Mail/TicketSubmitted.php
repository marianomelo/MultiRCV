<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $ticketSubject;
    public $ticketMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $subject, $message)
    {
        $this->user = $user;
        $this->ticketSubject = $subject;
        $this->ticketMessage = $message;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ticket de Soporte - MultiRCV: ' . $this->ticketSubject,
            replyTo: [$this->user->email],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-submitted',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
