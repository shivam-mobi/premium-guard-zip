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
        public ContactUs $contact,
        public string $ipAddress,
    ) {
    }

    public function build(): self
    {
        $name = trim($this->contact->first_name . ' ' . $this->contact->last_name);
        $phone = $this->contact->phone_number ?? '';
        $dateTime = $this->contact->created_at?->timezone(config('app.timezone'))->format('M j, Y \a\t g:i A')
            ?? now()->format('M j, Y \a\t g:i A');

        return $this
            ->subject('New contact form submission — ' . ($this->contact->company_name ?: $name))
            ->replyTo($this->contact->email, $name !== '' ? $name : null)
            ->view('emails.contact_us_notification', [
                'name' => $name,
                'phone' => $phone,
                'dateTime' => $dateTime,
                'ipAddress' => $this->ipAddress,
            ]);
    }
}
