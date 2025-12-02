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
                // DUMMY DATA FOR TESTING (As requested)
                // This simulates the data required for the billing dashboard
                
                $data = [
                    'overview' => [
                        'total_revenue' => 124500.00,
                        'trainer_payouts' => 45200.00,
                        'pending_payments' => 12800.00
                    ],
                    'recent_transactions' => [
                        [
                            'id' => 'txn_1001',
                            'type' => 'Client Payment',
                            'amount' => 150.00,
                            'currency' => 'USD',
                            'date' => now()->subHours(2)->toISOString(),
                            'status' => 'success',
                            'user' => [
                                'id' => 101,
                                'name' => 'Sarah Jenkins',
                                'role' => 'Client',
                                'profile_image' => null
                            ]
                        ],
                        [
                            'id' => 'payout_501',
                            'type' => 'Trainer Payout',
                            'amount' => 1200.00,
                            'currency' => 'USD',
                            'date' => now()->subHours(5)->toISOString(),
                            'status' => 'completed',
                            'user' => [
                                'id' => 55,
                                'name' => 'Mike Thor',
                                'role' => 'Trainer',
                                'profile_image' => null
                            ]
                        ],
                        [
                            'id' => 'txn_1002',
                            'type' => 'Client Payment',
                            'amount' => 200.00,
                            'currency' => 'USD',
                            'date' => now()->subDay()->toISOString(),
                            'status' => 'success',
                            'user' => [
                                'id' => 102,
                                'name' => 'John Doe',
                                'role' => 'Client',
                                'profile_image' => null
                            ]
                        ],
                        [
                            'id' => 'payout_502',
                            'type' => 'Trainer Payout',
                            'amount' => 850.00,
                            'currency' => 'USD',
                            'date' => now()->subDays(2)->toISOString(),
                            'status' => 'processing',
                            'user' => [
                                'id' => 56,
                                'name' => 'Jessica Fit',
                                'role' => 'Trainer',
                                'profile_image' => null
                            ]
                        ],
                        [
                            'id' => 'txn_1003',
                            'type' => 'Client Payment',
                            'amount' => 75.00,
                            'currency' => 'USD',
                            'date' => now()->subDays(2)->toISOString(),
                            'status' => 'success',
                            'user' => [
                                'id' => 103,
                                'name' => 'Emily Blunt',
                                'role' => 'Client',
                                'profile_image' => null
                            ]
                        ]
                    ]
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
