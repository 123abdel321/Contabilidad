<?php

namespace App\Helpers;

use Carbon\Carbon;

class Wompi
{
    private $publicKey;
    private $privateKey;
    private $endPoint;

    public function __construct()
    {
        $this->publicKey  = config('services.wompi.public_key');
        $this->privateKey = config('services.wompi.private_key');

        // 👇 Temporal: forzar el endpoint
        $this->endPoint = 'https://sandbox.wompi.co/v1';
    }

    /**
     * Obtener acceptance token del merchant
     */
    public function acceptanceToken()
    {
        $options = [
            CURLOPT_URL            => $this->endPoint . '/merchants/info',
            CURLOPT_HTTPHEADER     => [
                'Accept: */*',
                'x-merchant-public-key: ' . $this->publicKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($res, true);

        if ($httpCode === 200 && isset($res['data']['presigned_acceptance']['acceptance_token'])) {
            return $res['data']['presigned_acceptance']['acceptance_token'];
        }

        return null;
    }

    /**
     * Crear Payment Source (usando el token de la tarjeta)
     * Llave PRIVADA - solo desde el backend
     */
    public function createPaymentSource($cardToken, $customerEmail, $acceptanceToken)
    {
        $data = [
            'type'             => 'CARD',
            'token'            => $cardToken,
            'customer_email'   => $customerEmail,
            'acceptance_token' => $acceptanceToken,
        ];

        $options = [
            CURLOPT_URL            => $this->endPoint . '/payment_sources',
            CURLOPT_HTTPHEADER     => [
                'Accept: */*',
                'Authorization: Bearer ' . $this->privateKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => 59,
            CURLOPT_TIMEOUT        => 59,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($res, true);

        if ($httpCode >= 200 && $httpCode <= 299) {
            return (object)[
                'success' => true,
                'data'    => $res['data'],
            ];
        }

        return (object)[
            'success' => false,
            'message' => $res['error']['messages'] ?? 'Error creando payment source',
            'raw'     => $res,
        ];
    }

    /**
     * Obtener una transacción por ID
     */
    public function obtenerTransaccion($transactionId)
    {
        $options = [
            CURLOPT_URL            => $this->endPoint . '/transactions/' . $transactionId,
            CURLOPT_HTTPHEADER     => [
                'Accept: */*',
                'Authorization: Bearer ' . $this->privateKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($res, true);

        if ($httpCode >= 200 && $httpCode <= 299) {
            return (object)[
                'success' => true,
                'data'    => $res['data'],
            ];
        }

        return (object)[
            'success' => false,
            'message' => $res['error']['messages'] ?? 'Error consultando transacción',
        ];
    }

    /**
     * Crear transacción usando payment_source_id
     * Llave PRIVADA - solo desde el backend
     */
    public function createTransaction($paymentSourceId, $amountInCents, $customerEmail, $reference)
    {
        $acceptanceToken = $this->acceptanceToken();

        if (!$acceptanceToken) {
            return (object)[
                'success' => false,
                'message' => 'No se pudo obtener acceptance token',
            ];
        }

        $data = [
            'acceptance_token'  => $acceptanceToken,
            'amount_in_cents'   => $amountInCents,
            'currency'          => 'COP',
            'customer_email'    => $customerEmail,
            'reference'         => $reference,
            'payment_source_id' => (int) $paymentSourceId,
        ];

        $options = [
            CURLOPT_URL            => $this->endPoint . '/transactions',
            CURLOPT_HTTPHEADER     => [
                'Accept: */*',
                'Authorization: Bearer ' . $this->privateKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => 59,
            CURLOPT_TIMEOUT        => 59,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $res = json_decode($res, true);

        if ($httpCode >= 200 && $httpCode <= 299) {
            return (object)[
                'success' => true,
                'data'    => $res['data'],
            ];
        }

        return (object)[
            'success' => false,
            'message' => $res['error']['messages'] ?? 'Error creando transacción',
            'raw'     => $res,
        ];
    }
}