<?php

namespace App\Mail;

use App\Models\ContactUs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactUs $contact
    ) {
    }

    public function build(): self
    {
        $name = trim($this->contact->first_name . ' ' . $this->contact->last_name);

        return $this
            ->subject('New contact form submission — ' . ($this->contact->company_name ?: $name))
            ->replyTo($this->contact->email, $name !== '' ? $name : null)
            ->view('emails.contact_us_notification');
    }
}
