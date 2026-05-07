<?php

namespace App\Mail;

use App\Helpers\Logger;

/**
 * Mailer – Simple email sender using PHP mail() or SMTP.
 * In production, swap the _sendSmtp driver for PHPMailer/Symfony Mailer.
 */
class Mailer
{
    private string $driver;
    private array  $config;

    public function __construct()
    {
        $this->driver = env('MAIL_DRIVER', 'log');
        $this->config = [
            'host'      => env('MAIL_HOST', ''),
            'port'      => (int) env('MAIL_PORT', 587),
            'user'      => env('MAIL_USER', ''),
            'pass'      => env('MAIL_PASS', ''),
            'from'      => env('MAIL_FROM', 'noreply@realestate.com'),
            'from_name' => env('MAIL_FROM_NAME', 'NextGen Real Estate'),
        ];
    }

    // ------------------------------------------------------------------ //
    //  Public send methods                                                 //
    // ------------------------------------------------------------------ //

    public function sendOtp(string $toEmail, string $toName, string $otp): bool
    {
        $subject = 'Your Verification OTP – ' . env('APP_NAME', 'Real Estate');
        $body    = $this->otpTemplate($toName, $otp);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendPasswordResetOtp(string $toEmail, string $toName, string $otp): bool
    {
        $subject = 'Password Reset OTP – ' . env('APP_NAME', 'Real Estate');
        $body    = $this->passwordResetTemplate($toName, $otp);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendWelcome(string $toEmail, string $toName): bool
    {
        $subject = 'Welcome to ' . env('APP_NAME', 'Real Estate') . '!';
        $body    = $this->welcomeTemplate($toName);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    // ------------------------------------------------------------------ //
    //  Core send                                                           //
    // ------------------------------------------------------------------ //

    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        return match ($this->driver) {
            'smtp'  => $this->sendPhpMail($toEmail, $toName, $subject, $body),
            'log'   => $this->logEmail($toEmail, $toName, $subject, $body),
            default => $this->logEmail($toEmail, $toName, $subject, $body),
        };
    }

    // ------------------------------------------------------------------ //
    //  Drivers                                                             //
    // ------------------------------------------------------------------ //

    private function sendPhpMail(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $from    = $this->config['from'];
        $fromName = $this->config['from_name'];

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $result = @mail(
            "$toName <$toEmail>",
            '=?UTF-8?B?' . base64_encode($subject) . '?=',
            $body,
            $headers
        );

        if (!$result) {
            Logger::error("Mail failed to $toEmail");
        }
        return $result;
    }

    /**
     * Log-only driver: writes email to a log file (great for local dev).
     */
    private function logEmail(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $logFile = ROOT_PATH . '/storage/logs/' . date('Y-m-d') . '_mail.log';
        $entry   = "[" . date('Y-m-d H:i:s') . "]\n"
            . "To: $toName <$toEmail>\n"
            . "Subject: $subject\n"
            . "Body:\n$body\n"
            . str_repeat('-', 60) . "\n";

        file_put_contents($logFile, $entry, FILE_APPEND);
        return true;
    }

    // ------------------------------------------------------------------ //
    //  Email templates                                                     //
    // ------------------------------------------------------------------ //

    private function otpTemplate(string $name, string $otp): string
    {
        $appName = env('APP_NAME', 'Real Estate');
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px}
            .card{background:#fff;max-width:480px;margin:0 auto;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1)}
            .header{background:#2563eb;padding:24px;text-align:center;color:#fff}
            .body{padding:32px}
            .otp{font-size:40px;font-weight:800;letter-spacing:10px;color:#2563eb;text-align:center;padding:20px;background:#eff6ff;border-radius:8px;margin:24px 0}
            .footer{text-align:center;color:#888;font-size:12px;padding:16px}
        </style></head>
        <body>
        <div class="card">
            <div class="header"><h2>$appName</h2></div>
            <div class="body">
                <p>Hello, <strong>$name</strong>!</p>
                <p>Your email verification OTP is:</p>
                <div class="otp">$otp</div>
                <p>This code expires in <strong>15 minutes</strong>. Do not share it with anyone.</p>
            </div>
            <div class="footer">&copy; " . date('Y') . " $appName. If you didn't request this, ignore this email.</div>
        </div>
        </body></html>
        HTML;
    }

    private function passwordResetTemplate(string $name, string $otp): string
    {
        $appName = env('APP_NAME', 'Real Estate');
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"><style>
            body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px}
            .card{background:#fff;max-width:480px;margin:0 auto;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1)}
            .header{background:#dc2626;padding:24px;text-align:center;color:#fff}
            .body{padding:32px}
            .otp{font-size:40px;font-weight:800;letter-spacing:10px;color:#dc2626;text-align:center;padding:20px;background:#fef2f2;border-radius:8px;margin:24px 0}
            .footer{text-align:center;color:#888;font-size:12px;padding:16px}
        </style></head>
        <body>
        <div class="card">
            <div class="header"><h2>Password Reset</h2></div>
            <div class="body">
                <p>Hello, <strong>$name</strong>!</p>
                <p>Your password reset OTP is:</p>
                <div class="otp">$otp</div>
                <p>This code expires in <strong>15 minutes</strong>. If you didn't request this, secure your account immediately.</p>
            </div>
            <div class="footer">&copy; $appName</div>
        </div>
        </body></html>
        HTML;
    }

    private function welcomeTemplate(string $name): string
    {
        $appName = env('APP_NAME', 'Real Estate');
        $appUrl  = env('APP_URL', '');
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px">
        <div style="background:#fff;max-width:480px;margin:0 auto;border-radius:8px;overflow:hidden">
            <div style="background:#2563eb;padding:24px;text-align:center;color:#fff"><h2>Welcome to $appName!</h2></div>
            <div style="padding:32px">
                <p>Hi <strong>$name</strong>, your account is ready.</p>
                <p><a href="$appUrl/properties" style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block;margin-top:16px">Browse Properties</a></p>
            </div>
        </div>
        </body></html>
        HTML;
    }
}
