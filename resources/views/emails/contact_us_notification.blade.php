<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New contact request</title>
</head>
<body style="margin:0;padding:24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#111827;">
    <p style="margin:0 0 16px;">Hello Admin,</p>

    <p style="margin:0 0 16px;">A new contact request has been submitted on the website. Please find the details below:</p>

    <p style="margin:0 0 8px;"><strong>Name:</strong> {{ $name }}</p>
    <p style="margin:0 0 8px;"><strong>Email:</strong> <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>
    <p style="margin:0 0 16px;"><strong>Phone:</strong> {{ $phone ?: '—' }}</p>

    <p style="margin:0 0 8px;"><strong>Message:</strong></p>
    <p style="margin:0 0 24px;white-space:pre-wrap;">{{ $contact->message }}</p>

    <hr style="border:none;border-top:1px solid #e5e7eb;margin:24px 0;">

    <p style="margin:0 0 8px;"><strong>Submitted On:</strong> {{ $dateTime }}</p>

    <p style="margin:0 0 16px;">Please review the request and respond to the user accordingly.</p>

    <p style="margin:0;">Best regards,<br>
    <strong>System Notification</strong><br>
    {{ $contact->company_name }}</p>
</body>
</html>
