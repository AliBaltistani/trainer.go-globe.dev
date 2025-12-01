<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Transaction;
use App\Models\Payout;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Admin Billing API Controller
 * 
 * Handles billing and payment statistics and transaction history
 * 
 * @package     Laravel CMS App
 * @subpackage  Controllers\Api
 * @category    Billing
 * @author      Go Globe CMS Team
 * @since       1.0.0
 */
class AdminBillingController extends ApiBaseController
{
    /**
     * Get billing overview and recent transactions
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // 1. Calculate Overview Statistics
            
            // Total Revenue: Sum of all successful transactions (incoming payments)
            $totalRevenue = Transaction::where('status', 'success')
                ->sum('amount');

            // Trainer Payouts: Sum of all completed payouts
            $trainerPayouts = Payout::where('payout_status', 'completed')
                ->sum('amount');

            // Pending Payments: Sum of unpaid invoices (money owed to platform/trainers)
            // OR Pending Payouts (money platform owes to trainers)
            // Based on the image showing a positive amount, it could be either.
            // Let's assume it tracks pending payouts to trainers for now, or pending invoice payments.
            // A safe bet for "Pending Payments" in an admin dashboard is usually money waiting to be cleared or paid out.
            // Let's use Pending Payouts + Unpaid Invoices
            
            $pendingPayouts = Payout::where('payout_status', 'processing')->sum('amount');
            $pendingInvoices = Invoice::where('status', 'pending')->sum('total_amount');
            
            // Interpreting "Pending Payments" as pending money movements. 
            // If the client wants specific logic, we can adjust. For now, let's sum pending payouts.
            $pendingPayments = $pendingPayouts;


            // 2. Fetch Recent Transactions
            // We need to merge Client Payments (Transactions) and Trainer Payouts (Payouts)
            
            // Fetch recent Client Payments
            $clientPayments = Transaction::where('status', 'success')
                ->with(['invoice.client']) // Load client via invoice
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($transaction) {
                    $client = $transaction->invoice && $transaction->invoice->client ? $transaction->invoice->client : null;
                    // Fallback if client_id is directly on transaction (which it is based on model)
                    if (!$client && $transaction->client_id) {
                        $client = User::find($transaction->client_id);
                    }

                    return [
                        'id' => 'txn_' . $transaction->id,
                        'type' => 'Client Payment',
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                        'date' => $transaction->created_at->toISOString(),
                        'user' => $client ? [
                            'id' => $client->id,
                            'name' => $client->name,
                            'role' => 'Client',
                            'profile_image' => $client->profile_image ? asset('storage/' . $client->profile_image) : null,
                        ] : [
                            'id' => null,
                            'name' => 'Unknown Client',
                            'role' => 'Client',
                            'profile_image' => null
                        ]
                    ];
                });

            // Fetch recent Trainer Payouts
            $trainerPayoutsList = Payout::with(['trainer'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($payout) {
                    return [
                        'id' => 'payout_' . $payout->id,
                        'type' => 'Trainer Payout',
                        'amount' => $payout->amount,
                        'currency' => $payout->currency,
                        'date' => $payout->created_at->toISOString(),
                        'user' => $payout->trainer ? [
                            'id' => $payout->trainer->id,
                            'name' => $payout->trainer->name,
                            'role' => 'Trainer',
                            'profile_image' => $payout->trainer->profile_image ? asset('storage/' . $payout->trainer->profile_image) : null,
                        ] : [
                            'id' => null,
                            'name' => 'Unknown Trainer',
                            'role' => 'Trainer',
                            'profile_image' => null
                        ]
                    ];
                });

            // Merge and sort
            $allTransactions = $clientPayments->merge($trainerPayoutsList)
                ->sortByDesc('date')
                ->values()
                ->take(10); // Return top 10 mixed

            $data = [
                'overview' => [
                    'total_revenue' => $totalRevenue,
                    'trainer_payouts' => $trainerPayouts,
                    'pending_payments' => $pendingPayments
                ],
                'recent_transactions' => $allTransactions
            ];

            return $this->sendResponse($data, 'Billing and payment details retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Billing API Error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve billing details', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all transactions with pagination and filtering
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions(Request $request)
    {
        try {
            $query = Transaction::with(['invoice.client', 'invoice.trainer']);

            if ($request->filled('status')) {
                $query->where('status', $request->string('status'));
            }

            $transactions = $query->latest()->paginate(20);

            return $this->sendResponse($transactions, 'Transactions retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Transactions API Error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve transactions', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all payouts with pagination and filtering
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payouts(Request $request)
    {
        try {
            $query = Payout::with(['trainer']);

            if ($request->filled('status')) {
                $query->where('payout_status', $request->string('status'));
            }

            $payouts = $query->latest()->paginate(20);

            return $this->sendResponse($payouts, 'Payouts retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Payouts API Error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve payouts', ['error' => $e->getMessage()], 500);
        }
    }
}
