<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\PaymentGateway;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PayPalPaymentService
{
    protected PayPalHttpClient $client;
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $gateway = PaymentGateway::where('type', 'paypal')->where('enabled', true)->firstOrFail();
        $this->clientId = $gateway->public_key ?? '';
        $this->clientSecret = $gateway->secret_key ?? '';
        $this->baseUrl = 'https://api-m.sandbox.paypal.com'; // Default to sandbox, should probably be configurable or based on gateway mode
        
        // Check if we are in live mode (simplified check, ideally added to gateway model)
        if (($gateway->mode ?? 'sandbox') === 'live') {
            $this->baseUrl = 'https://api-m.paypal.com';
        }

        $env = new SandboxEnvironment($this->clientId, $this->clientSecret);
        // If live, we'd use ProductionEnvironment, but for now let's stick to the existing logic + new Payout logic
        $this->client = new PayPalHttpClient($env);
    }

    public function createOrder(Invoice $invoice, string $returnUrl = '', string $cancelUrl = ''): array
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $returnUrl = $returnUrl !== '' ? $returnUrl : rtrim((string) config('app.url'), '/') . '/api/payment/paypal/return?invoice=' . $invoice->id;
        $cancelUrl = $cancelUrl !== '' ? $cancelUrl : rtrim((string) config('app.url'), '/') . '/api/payment/paypal/cancel?invoice=' . $invoice->id;
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string) $invoice->id,
                'amount' => [
                    'value' => number_format($invoice->total_amount, 2, '.', ''),
                    'currency_code' => strtoupper($invoice->currency),
                ],
            ]],
            'application_context' => [
                'brand_name' => config('app.name'),
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ];
        $response = $this->client->execute($request);
        $approve = null;
        foreach ($response->result->links ?? [] as $link) {
            if (($link->rel ?? '') === 'approve') {
                $approve = $link->href;
                break;
            }
        }
        return ['id' => $response->result->id ?? null, 'approve_url' => $approve];
    }

    public function captureOrder(string $orderId): array
    {
        $request = new OrdersCaptureRequest($orderId);
        $request->prefer('return=representation');
        $response = $this->client->execute($request);
        return ['status' => $response->statusCode ?? null, 'result' => $response->result ?? null];
    }

    /**
     * Send a payout to a recipient email
     * 
     * @param float $amount
     * @param string $email
     * @param string $currency
     * @param string|null $referenceId
     * @return array
     * @throws \Exception
     */
    public function sendPayout(float $amount, string $email, string $currency, ?string $referenceId = null): array
    {
        $accessToken = $this->getAccessToken();
        $referenceId = $referenceId ?? Str::uuid()->toString();
        $batchId = 'batch_' . Str::random(10);

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v1/payments/payouts", [
                'sender_batch_header' => [
                    'sender_batch_id' => $batchId,
                    'email_subject' => 'You have a payout!',
                    'email_message' => 'You have received a payout from ' . config('app.name'),
                ],
                'items' => [[
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency' => strtoupper($currency),
                    ],
                    'note' => 'Payout',
                    'sender_item_id' => $referenceId,
                    'receiver' => $email,
                ]]
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'id' => $data['batch_header']['payout_batch_id'] ?? null,
                'status' => $data['batch_header']['batch_status'] ?? 'pending',
                'details' => $data
            ];
        }

        throw new \Exception('PayPal Payout Failed: ' . $response->body());
    }

    protected function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        throw new \Exception('Failed to get PayPal Access Token');
    }

    public function refundOrder(string $captureId, ?float $amount = null, string $currency = 'USD'): array
    {
        $accessToken = $this->getAccessToken();
        $url = "{$this->baseUrl}/v2/payments/captures/{$captureId}/refund";
        
        $body = [];
        if ($amount) {
            $body['amount'] = [
                'value' => number_format($amount, 2, '.', ''),
                'currency_code' => strtoupper($currency)
            ];
        }

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json']) // Explicitly set content type
            ->post($url, empty($body) ? new \stdClass() : $body); // Send empty object if body is empty

        if ($response->successful()) {
            return ['id' => $response->json()['id'], 'status' => $response->json()['status']];
        }
        
        throw new \Exception('PayPal Refund Failed: ' . $response->body());
    }
}
