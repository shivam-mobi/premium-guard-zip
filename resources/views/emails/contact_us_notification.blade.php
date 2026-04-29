<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>New Contact Request</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4; padding:20px;">
    <tr>
      <td align="center">

        <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

          <!-- Header -->
          <tr>
            <td style="background:#2c3e50; color:#ffffff; padding:15px 20px; font-size:18px; font-weight:bold;">
              New Contact Request Submitted
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:20px; color:#333333; font-size:14px; line-height:1.6;">
              <p>Hello Admin,</p>

              <p>A new contact request has been submitted on the website. Please find the details below:</p>

              <table width="100%" cellpadding="8" cellspacing="0" border="0" style="border-collapse:collapse; margin-top:10px;">
                <tr>
                  <td width="28%" style="border:1px solid #dddddd; background:#f9f9f9; font-weight:bold;">Name</td>
                  <td style="border:1px solid #dddddd;">{{ $name }}</td>
                </tr>
                <tr>
                  <td style="border:1px solid #dddddd; background:#f9f9f9; font-weight:bold;">Email</td>
                  <td style="border:1px solid #dddddd;"><a href="mailto:{{ $contact->email }}" style="color:#333333;">{{ $contact->email }}</a></td>
                </tr>
                <tr>
                  <td style="border:1px solid #dddddd; background:#f9f9f9; font-weight:bold;">Phone</td>
                  <td style="border:1px solid #dddddd;">{{ $phone !== '' ? $phone : '—' }}</td>
                </tr>
                <tr>
                  <td style="border:1px solid #dddddd; background:#f9f9f9; font-weight:bold; vertical-align:top;">Message</td>
                  <td style="border:1px solid #dddddd; white-space:pre-wrap;">{{ $contact->message }}</td>
                </tr>
              </table>

              <p style="margin-top:20px;">Please review and take the necessary action.</p>

              <p>Best regards,<br>Premium Guard</p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#ecf0f1; text-align:center; padding:10px; font-size:12px; color:#777;">
              This is an automated email. Please do not reply.
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
