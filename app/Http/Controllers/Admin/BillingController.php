<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\User;
use App\Models\TrainerBankAccount;
use App\Services\Payments\StripePaymentService;
use App\Services\Payments\PayPalPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function invoices(Request $request)
    {
        $query = Invoice::with(['client', 'trainer']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        $invoices = $query->latest()->paginate(20)->appends($request->query());

        $stats = [
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'total_collected' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_count' => Invoice::where('status', 'pending')->count(),
        ];

        return view('admin.billing.invoices.index', compact('invoices', 'stats'));
    }

    public function payouts(Request $request)
    {
        $query = Payout::with(['trainer']);
        if ($request->filled('status')) {
            $query->where('payout_status', $request->string('status'));
        }
        $payouts = $query->latest()->paginate(20)->appends($request->query());

        $stats = [
            'total_payouts' => Payout::count(),
            'total_paid' => Payout::where('payout_status', 'completed')->sum('amount'),
            'pending_amount' => Payout::where('payout_status', 'processing')->sum('amount'),
            'pending_count' => Payout::where('payout_status', 'processing')->count(),
        ];

        return view('admin.billing.payouts.index', compact('payouts', 'stats'));
    }

    public function transactions(Request $request)
    {
        // Merge Client Payments (Transactions) and Trainer Payouts (Payouts) for a unified view
        // This is a bit complex for pagination, so for now we might just show Client Payments (Transactions) 
        // OR we can use a Union if schemas align, or just paginate Transactions table if that's the main focus.
        // The user asked for "transactions", usually implies incoming money (Transactions table).
        // Let's stick to the Transaction model for the full list, as Payouts has its own list.
        
        $query = Transaction::with(['invoice.client', 'invoice.trainer', 'gateway']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        
        $transactions = $query->latest()->paginate(20)->appends($request->query());
        
        $stats = [
            'total_transactions' => Transaction::count(),
            'success_count' => Transaction::where('status', 'success')->count(),
            'total_revenue' => Transaction::where('status', 'success')->sum('amount'),
            'failed_count' => Transaction::where('status', 'failed')->count(),
        ];
        
        return view('admin.billing.transactions.index', compact('transactions', 'stats'));
    }

    public function exportPayouts()
    {
        $fileName = 'payouts_export_' . date('Y-m-d') . '.csv';
        $payouts = Payout::with('trainer')->latest()->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Trainer Name', 'Amount', 'Currency', 'Status', 'Date');

        $callback = function() use($payouts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payouts as $payout) {
                $row['ID']  = $payout->id;
                $row['Trainer Name']    = $payout->trainer->name ?? 'N/A';
                $row['Amount']    = $payout->amount;
                $row['Currency']  = $payout->currency;
                $row['Status']  = $payout->payout_status;
                $row['Date']  = $payout->created_at;

                fputcsv($file, array($row['ID'], $row['Trainer Name'], $row['Amount'], $row['Currency'], $row['Status'], $row['Date']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function dashboard()
    {
        $totals = [
            'revenue' => Transaction::where('status', 'success')->sum('amount'),
            'pending_payouts' => Payout::where('payout_status', 'processing')->sum('amount'),
            'fees_collected' => Payout::sum('fee_amount'),
            'trainer_payouts' => Payout::where('payout_status', 'completed')->sum('amount'),
        ];

        // Recent Activity (Merged View)
        $clientPayments = Transaction::where('status', 'success')
            ->with(['invoice.client'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($t) {
                $client = $t->invoice->client ?? User::find($t->client_id);
                return [
                    'id' => $t->id,
                    'type' => 'payment',
                    'amount' => $t->amount,
                    'currency' => $t->currency,
                    'status' => $t->status,
                    'date' => $t->created_at,
                    'user' => $client,
                    'description' => 'Payment from ' . ($client->name ?? 'Client')
                ];
            });

        $trainerPayouts = Payout::with(['trainer'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'type' => 'payout',
                    'amount' => $p->amount,
                    'currency' => $p->currency,
                    'status' => $p->payout_status,
                    'date' => $p->created_at,
                    'user' => $p->trainer,
                    'description' => 'Payout to ' . ($p->trainer->name ?? 'Trainer')
                ];
            });

        $recentActivity = $clientPayments->merge($trainerPayouts)->sortByDesc('date')->take(10);

        return view('admin.billing.dashboard', compact('totals', 'recentActivity'));
    }

    public function processPayout($id)
    {
        $payout = Payout::with('trainer')->findOrFail($id);

        if ($payout->payout_status === 'completed') {
            return back()->with('error', 'Payout is already completed.');
        }

        $trainer = $payout->trainer;
        if (!$trainer) {
            return back()->with('error', 'Trainer not found.');
        }

        // Find verified bank account
        $bankAccount = TrainerBankAccount::where('trainer_id', $trainer->id)
            ->where('verification_status', 'verified')
            ->latest()
            ->first();

        if (!$bankAccount) {
            return back()->with('error', 'No verified bank account linked for this trainer.');
        }

        try {
            $payout->payout_status = 'processing';
            $payout->save();

            if ($bankAccount->gateway === 'stripe') {
                $service = new StripePaymentService();
                $result = $service->transferToConnectAccount(
                    (float)$payout->amount, 
                    $payout->currency, 
                    $bankAccount->account_id
                );
                $payout->gateway_payout_id = $result['id'];
                $payout->payout_status = 'completed'; // Stripe transfers are usually instant
            } elseif ($bankAccount->gateway === 'paypal') {
                $service = new PayPalPaymentService();
                $result = $service->sendPayout(
                    (float)$payout->amount, 
                    $bankAccount->account_id,
                    $payout->currency
                );
                $payout->gateway_payout_id = $result['id'];
                $payout->payout_status = 'processing'; // PayPal payouts are pending initially
            } else {
                throw new \Exception('Unsupported gateway: ' . $bankAccount->gateway);
            }
            
            $payout->save();

            return back()->with('success', 'Payout processed successfully via ' . ucfirst($bankAccount->gateway));

        } catch (\Exception $e) {
            Log::error('Payout Processing Failed: ' . $e->getMessage());
            $payout->payout_status = 'failed';
            $payout->save();
            return back()->with('error', 'Payout failed: ' . $e->getMessage());
        }
    }

    public function refundTransaction($id)
    {
        $transaction = Transaction::with(['invoice', 'gateway'])->findOrFail($id);

        if ($transaction->status === 'refunded') {
            return back()->with('error', 'Transaction is already refunded.');
        }

        $gatewayType = $transaction->gateway->type ?? null;

        try {
            if ($gatewayType === 'stripe') {
                $service = new StripePaymentService();
                $service->refundPayment($transaction->transaction_id);
            } elseif ($gatewayType === 'paypal') {
                $service = new PayPalPaymentService();
                $service->refundOrder($transaction->transaction_id, null, $transaction->currency);
            } else {
                throw new \Exception('Unsupported or missing gateway for refund: ' . ($gatewayType ?? 'N/A'));
            }

            $transaction->status = 'refunded';
            $transaction->save();
            
            if ($transaction->invoice) {
                $transaction->invoice->status = 'refunded';
                $transaction->invoice->save();
            }

            return back()->with('success', 'Transaction refunded successfully.');

        } catch (\Exception $e) {
            Log::error('Refund Failed: ' . $e->getMessage());
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }
}
