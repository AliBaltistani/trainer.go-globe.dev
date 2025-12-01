<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\Payout;
use App\Models\WebhookLog;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\StripeClient;

class ProcessStripeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(NotificationService $notificationService): void
    {
        $type = $this->payload['type'] ?? null;
        $data = $this->payload['data']['object'] ?? [];

        if ($type === 'payment_intent.succeeded') {
            $intentId = $data['id'] ?? null;
            if (!$intentId) return;
            $txn = Transaction::where('transaction_id', $intentId)->first();
            if ($txn) {
                $txn->status = 'paid';
                $txn->response = $data;
                $txn->save();
                $invoice = Invoice::find($txn->invoice_id);
                if ($invoice && $invoice->status !== 'paid') {
                    $invoice->status = 'paid';
                    $invoice->save();
                    $net = max(0, ($txn->amount ?? 0));
                    Payout::create([
                        'trainer_id' => $txn->trainer_id,
                        'amount' => $net,
                        'currency' => $txn->currency,
                        'fee_amount' => 0,
                        'payout_status' => 'processing',
                    ]);

                    // Notify Trainer
                    $trainer = User::find($txn->trainer_id);
                    if ($trainer) {
                         $notificationService->notifyPaymentStatus($trainer, 'Paid', $txn->id);
                    }
                    // Notify Client
                    $client = User::find($invoice->client_id);
                    if ($client) {
                         $notificationService->notifyPaymentStatus($client, 'Paid', $txn->id);
                    }
                }
            }
        } elseif ($type === 'payment_intent.payment_failed') {
            $intentId = $data['id'] ?? null;
            $txn = Transaction::where('transaction_id', $intentId)->first();
            if ($txn) {
                $txn->status = 'failed';
                $txn->response = $data;
                $txn->save();
                $invoice = Invoice::find($txn->invoice_id);
                if ($invoice && $invoice->status !== 'failed') {
                    $invoice->status = 'failed';
                    $invoice->save();

                    // Notify Client
                    $client = User::find($invoice->client_id);
                    if ($client) {
                         $notificationService->notifyPaymentStatus($client, 'Failed', $txn->id);
                    }
                }
            }
        }
    }
}
