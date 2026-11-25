<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends transactional emails via Resend HTTP API (when RESEND_API_KEY is configured)
 * or falls back to Laravel Mail (SMTP/log). Returns true when delivered to an external provider.
 */
final class TransactionalMailer
{
    public function sendVerificationCode(string $toEmail, string $customerName, string $code): bool
    {
        $subject = 'Vevey Doces — Código de verificação da sua conta';
        $text = "Olá, {$customerName}!\n\nSeu código de verificação Vevey Doces é: {$code}\n\nEste código expira em 15 minutos.";
        $html = "<div style=\"font-family:Inter,Arial,sans-serif;max-width:480px;margin:0 auto;padding:24px;background:#FDFBF7;border:1px solid #E5DDD0;border-radius:16px;color:#1F1B16\">"
            . "<h2 style=\"margin:0 0 8px;color:#E11D48\">Vevey Doces</h2>"
            . "<p style=\"margin:0 0 16px;font-size:14px\">Olá, <strong>" . e($customerName) . "</strong>! Use o código abaixo para confirmar sua conta:</p>"
            . "<div style=\"font-family:monospace;font-size:28px;font-weight:700;letter-spacing:8px;text-align:center;padding:16px;background:#F7F4EE;border-radius:12px;margin-bottom:16px\">{$code}</div>"
            . "<p style=\"margin:0;font-size:12px;color:#7B7266\">Este código expira em 15 minutos. Se você não solicitou este cadastro, ignore este e-mail.</p>"
            . '</div>';

        $resendKey = (string) config('services.resend.key', '');
        if ($resendKey !== '') {
            try {
                $from = (string) config('services.resend.from', 'Vevey Doces <onboarding@resend.dev>');
                $response = Http::timeout(6)
                    ->withToken($resendKey)
                    ->acceptJson()
                    ->post('https://api.resend.com/emails', [
                        'from' => $from,
                        'to' => [$toEmail],
                        'subject' => $subject,
                        'html' => $html,
                        'text' => $text,
                    ]);

                if ($response->successful()) {
                    return true;
                }

                Log::warning('Resend API retornou erro: ' . $response->body());
            } catch (Throwable $e) {
                Log::warning('Falha ao conectar na Resend API: ' . $e->getMessage());
            }
        }

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            Log::info("Código 2FA Vevey para {$toEmail}: {$code}");

            return false;
        }

        try {
            Mail::raw($text, static function ($message) use ($toEmail, $subject): void {
                $message->to($toEmail)->subject($subject);
            });

            return true;
        } catch (Throwable $e) {
            Log::warning('Falha ao enviar e-mail SMTP: ' . $e->getMessage());

            return false;
        }
    }
}
