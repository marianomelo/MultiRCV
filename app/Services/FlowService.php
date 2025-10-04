<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlowService
{
    protected $apiKey;
    protected $secretKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('flow.api_key');
        $this->secretKey = config('flow.secret_key');
        $this->apiUrl = config('flow.api_url');
    }

    /**
     * Create HMAC-SHA256 signature for Flow API
     */
    protected function sign(array $params): string
    {
        // Sort parameters alphabetically
        ksort($params);

        // Build string to sign
        $stringToSign = '';
        foreach ($params as $key => $value) {
            $stringToSign .= $key . $value;
        }

        // Generate signature
        return hash_hmac('sha256', $stringToSign, $this->secretKey);
    }

    /**
     * Create a payment in Flow
     */
    public function createPayment(array $paymentData): array
    {
        $params = [
            'apiKey' => $this->apiKey,
            'commerceOrder' => $paymentData['commerce_order'],
            'subject' => $paymentData['subject'],
            'currency' => $paymentData['currency'] ?? 'CLP',
            'amount' => $paymentData['amount'],
            'email' => $paymentData['email'],
            'urlConfirmation' => config('flow.url_confirmation'),
            'urlReturn' => config('flow.url_return'),
        ];

        // Add optional parameters
        if (isset($paymentData['optional'])) {
            $params['optional'] = $paymentData['optional'];
        }

        // Sign the request
        $params['s'] = $this->sign($params);

        try {
            $response = Http::asForm()->post($this->apiUrl . '/payment/create', $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Flow createPayment failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Error creating Flow payment: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Flow createPayment exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get payment status from Flow
     */
    public function getPaymentStatus(string $token): array
    {
        $params = [
            'apiKey' => $this->apiKey,
            'token' => $token,
        ];

        $params['s'] = $this->sign($params);

        try {
            $response = Http::get($this->apiUrl . '/payment/getStatus', $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Flow getPaymentStatus failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Error getting Flow payment status');
        } catch (\Exception $e) {
            Log::error('Flow getPaymentStatus exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate Flow callback signature
     */
    public function validateCallback(array $params): bool
    {
        if (!isset($params['s'])) {
            return false;
        }

        $signature = $params['s'];
        unset($params['s']);

        $expectedSignature = $this->sign($params);

        return hash_equals($expectedSignature, $signature);
    }
}
