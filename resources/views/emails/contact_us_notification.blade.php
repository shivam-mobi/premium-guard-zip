<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New contact form submission</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.5;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:24px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="padding:24px 28px;border-bottom:1px solid #e5e7eb;background:#111827;">
                            <p style="margin:0;font-size:13px;font-weight:600;letter-spacing:0.04em;color:#9ca3af;text-transform:uppercase;">PG Filters</p>
                            <p style="margin:8px 0 0;font-size:20px;font-weight:600;color:#ffffff;">New contact message</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                                        <span style="display:block;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Name</span>
                                        <span style="display:block;margin-top:4px;color:#111827;">{{ $contact->first_name }} {{ $contact->last_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                                        <span style="display:block;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Email</span>
                                        <span style="display:block;margin-top:4px;"><a href="mailto:{{ $contact->email }}" style="color:#2563eb;text-decoration:none;">{{ $contact->email }}</a></span>
                                    </td>
                                </tr>
                                @if(!empty($contact->phone_number))
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                                        <span style="display:block;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Phone</span>
                                        <span style="display:block;margin-top:4px;color:#111827;">{{ $contact->phone_number }}</span>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                                        <span style="display:block;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Company</span>
                                        <span style="display:block;margin-top:4px;color:#111827;">{{ $contact->company_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 0 0;">
                                        <span style="display:block;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.03em;">Message</span>
                                        <div style="margin-top:10px;padding:14px 16px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;color:#374151;white-space:pre-wrap;">{{ $contact->message }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 24px;font-size:12px;color:#9ca3af;border-top:1px solid #f3f4f6;">
                            Submitted {{ $contact->created_at?->timezone(config('app.timezone'))->format('M j, Y \a\t g:i A') ?? now()->format('M j, Y \a\t g:i A') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
