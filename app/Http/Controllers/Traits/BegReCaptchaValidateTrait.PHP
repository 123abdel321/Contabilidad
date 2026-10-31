<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


trait BegReCaptchaValidateTrait
{
    protected function validateReCaptcha(string $token, ?string $expectedAction = null, float $minScore = 0.5): array
    {
        $secret = config('services.recaptcha.secret_key') ?: env('RECAPTCHA_SECRET_KEY');

        if (!$secret) {
            Log::error('reCAPTCHA: secret key no configurada');
            return ['success' => false, 'score' => null, 'action' => null, 'error' => 'missing_secret'];
        }

        try {
            $response = Http::asForm()->timeout(5)->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'secret'   => $secret,
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]
            );

            if (!$response->ok()) {
                return ['success' => false, 'score' => null, 'action' => null, 'error' => 'http_error'];
            }

            $data = $response->json();

            if (empty($data['success'])) {
                Log::info('reCAPTCHA: token inválido', ['errors' => $data['error-codes'] ?? []]);
                return ['success' => false, 'score' => null, 'action' => null, 'error' => 'invalid_token'];
            }

            $score  = isset($data['score']) ? (float) $data['score'] : null;
            $action = $data['action'] ?? null;

            if ($expectedAction !== null && $action !== $expectedAction) {
                return ['success' => false, 'score' => $score, 'action' => $action, 'error' => 'action_mismatch'];
            }

            if ($score !== null && $score < $minScore) {
                return ['success' => false, 'score' => $score, 'action' => $action, 'error' => 'low_score'];
            }

            return ['success' => true, 'score' => $score, 'action' => $action, 'error' => null];

        } catch (\Throwable $e) {
            Log::error('reCAPTCHA excepción', ['msg' => $e->getMessage()]);
            return ['success' => false, 'score' => null, 'action' => null, 'error' => 'exception'];
        }
    }
}