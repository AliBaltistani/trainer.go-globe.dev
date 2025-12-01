<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\PaymentGateway;
use Stripe\StripeClient;

class StripePaymentService
{
    protected StripeClient $stripe;
    protected PaymentGateway $gateway;

    public function __construct()
    {
        $gateway = PaymentGateway::where('type', 'stripe')->where('enabled', true)->where('is_default', true)->firstOrFail();
        $this->gateway = $gateway;
        $this->stripe = new StripeClient($gateway->secret_key);
    }

    public function createPaymentIntent(Invoice $invoice, ?string $destinationAccountId = null, int $applicationFeePercent = 10): array
    {
        $amountCents = (int) round($invoice->total_amount * 100);
        $feeAmountCents = (int) floor($amountCents * ($applicationFeePercent / 100));

        $params = [
            'amount' => $amountCents,
            'currency' => strtolower($invoice->currency),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'invoice_id' => (string) $invoice->id,
                'client_id' => (string) $invoice->client_id,
                'trainer_id' => (string) $invoice->trainer_id,
            ],
        ];

        if ($destinationAccountId) {
            $params['transfer_data'] = ['destination' => $destinationAccountId];
            $params['application_fee_amount'] = $feeAmountCents;
        }

        $intent = $this->stripe->paymentIntents->create($params);

        return ['id' => $intent->id, 'client_secret' => $intent->client_secret];
    }

    public function createCheckoutSession(Invoice $invoice, ?string $destinationAccountId = null, int $applicationFeePercent = 10, string $successUrl = '', string $cancelUrl = ''): array
    {
        $amountCents = (int) round($invoice->total_amount * 100);
        $feeAmountCents = (int) floor($amountCents * ($applicationFeePercent / 100));

        $successUrl = $successUrl !== '' ? $successUrl : rtrim((string) config('app.url'), '/') . '/api/payment/stripe/return?invoice=' . $invoice->id;
        $successUrl = $successUrl . (str_contains($successUrl, '?') ? '&' : '?') . 'session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $cancelUrl !== '' ? $cancelUrl : rtrim((string) config('app.url'), '/') . '/api/payment/stripe/cancel?invoice=' . $invoice->id;

        $params = [
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($invoice->currency),
                    'product_data' => ['name' => 'Invoice #' . $invoice->id],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'payment_intent_data' => [
                'metadata' => [
                    'invoice_id' => (string) $invoice->id,
                    'client_id' => (string) $invoice->client_id,
                    'trainer_id' => (string) $invoice->trainer_id,
                ],
            ],
        ];

        if ($destinationAccountId) {
            $params['payment_intent_data']['transfer_data'] = ['destination' => $destinationAccountId];
            $params['payment_intent_data']['application_fee_amount'] = $feeAmountCents;
        }

        $session = $this->stripe->checkout->sessions->create($params);

        return ['id' => $session->id, 'url' => $session->url];
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        $session = $this->stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['payment_intent']]);
        return [
            'id' => $session->id,
            'payment_status' => $session->payment_status,
            'status' => $session->status,
            'url' => $session->url,
            'payment_intent' => $session->payment_intent,
        ];
    }

    /**
     * Transfer funds to a connected Stripe account
     * 
     * @param float $amount Amount in major units (e.g., 10.50)
     * @param string $currency Currency code (e.g., 'usd')
     * @param string $destinationAccountId Stripe Connect Account ID
     * @param string|null $transferGroup Optional transfer group for reconciliation
     * @return array Transfer details
     * @throws \Exception
     */
    public function transferToConnectAccount(float $amount, string $currency, string $destinationAccountId, ?string $transferGroup = null): array
    {
        $amountCents = (int) round($amount * 100);
        
        $params = [
            'amount' => $amountCents,
            'currency' => strtolower($currency),
            'destination' => $destinationAccountId,
        ];

        if ($transferGroup) {
            $params['transfer_group'] = $transferGroup;
        }

        try {
            $transfer = $this->stripe->transfers->create($params);
            return [
                'id' => $transfer->id,
                'amount' => $transfer->amount,
                'currency' => $transfer->currency,
                'destination' => $transfer->destination,
                'status' => 'success', // Transfers are usually instant
            ];
        } catch (\Exception $e) {
            throw new \Exception('Stripe Transfer Failed: ' . $e->getMessage());
        }
    }

    public function refundPayment(string $paymentIntentId, ?float $amount = null): array
    {
        $params = ['payment_intent' => $paymentIntentId];
        if ($amount) {
            $params['amount'] = (int) round($amount * 100);
        }
        $refund = $this->stripe->refunds->create($params);
        return ['id' => $refund->id, 'status' => $refund->status];
    }
}

