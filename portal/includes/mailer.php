<?php
/**
 * Minimal mail wrapper around PHP's built-in mail(), which cPanel hosts
 * generally support out of the box. If your host requires SMTP instead,
 * replace the body of send_mail() with your SMTP library of choice.
 */
function send_mail(string $toEmail, string $toName, string $subject, string $htmlBody): bool
{
    $from = MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$from}\r\n";
    $headers .= "Reply-To: " . MAIL_FROM_ADDRESS . "\r\n";

    $safeSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    return @mail($toEmail, $safeSubject, $htmlBody, $headers);
}

function devotional_email_html(string $recipientName, string $verse, string $translation, string $reflection, string $dateLabel): string
{
    $name = htmlspecialchars($recipientName ?: 'friend', ENT_QUOTES, 'UTF-8');
    return "
    <div style=\"font-family:Georgia,serif;background:#faf5ec;padding:32px;color:#241a15\">
      <div style=\"max-width:560px;margin:0 auto;background:#ffffff;border-radius:14px;padding:32px;border:1px solid #e8dcc6\">
        <p style=\"color:#d9711c;font-weight:bold;letter-spacing:.06em;text-transform:uppercase;font-size:12px\">Sundarakanda &middot; Daily Devotional &middot; {$dateLabel}</p>
        <p style=\"margin-top:18px\">Namaste {$name},</p>
        <blockquote style=\"margin:18px 0;padding:16px 20px;background:#f3e8d3;border-radius:10px;font-style:italic\">" . nl2br(htmlspecialchars($verse, ENT_QUOTES, 'UTF-8')) . "</blockquote>
        <p><strong>Meaning:</strong> " . nl2br(htmlspecialchars($translation, ENT_QUOTES, 'UTF-8')) . "</p>
        <p>" . nl2br(htmlspecialchars($reflection, ENT_QUOTES, 'UTF-8')) . "</p>
        <p style=\"margin-top:28px;font-size:13px;color:#7a685c\">You're receiving this because you subscribed to Sundarakanda's daily devotional. You can unsubscribe any time from your account, or via the link in this footer.</p>
      </div>
    </div>";
}

function member_welcome_email_html(string $recipientName, string $unsubscribeUrl): string
{
    $name = htmlspecialchars($recipientName ?: 'friend', ENT_QUOTES, 'UTF-8');
    $url = htmlspecialchars($unsubscribeUrl, ENT_QUOTES, 'UTF-8');
    return "
    <div style=\"font-family:Georgia,serif;background:#faf5ec;padding:32px;color:#241a15\">
      <div style=\"max-width:560px;margin:0 auto;background:#ffffff;border-radius:14px;padding:32px;border:1px solid #e8dcc6\">
        <p style=\"color:#d9711c;font-weight:bold;letter-spacing:.06em;text-transform:uppercase;font-size:12px\">Sundarakanda &middot; Welcome</p>
        <p style=\"margin-top:18px\">Namaste {$name},</p>
        <p>Thank you for joining Sundarakanda as a free member. You will now hear from us by email about upcoming events, Parayanam sessions, and community updates &mdash; no cost, no obligation.</p>
        <p>If you ever want to stop receiving these emails, you can do so any time using the link below.</p>
        <p style=\"margin-top:28px;font-size:13px;color:#7a685c\">You are receiving this because you registered as a Sundarakanda member. <a href=\"{$url}\" style=\"color:#d9711c\">Unsubscribe</a>.</p>
      </div>
    </div>";
}
