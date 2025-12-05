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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function invoices(Request $request)
    {
        if ($request->ajax()) {
            return $this->getInvoicesDataTable($request);
        }

        $stats = [
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'total_collected' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_count' => Invoice::where('status', 'pending')->count(),
        ];

        return view('admin.billing.invoices.index', compact('stats'));
    }

    public function showInvoice($id)
    {
        $invoice = Invoice::with(['client', 'trainer', 'items', 'transactions'])->findOrFail($id);
        return view('admin.billing.invoices.show', compact('invoice'));
    }

    public function payouts(Request $request)
    {
        if ($request->ajax()) {
            return $this->getPayoutsDataTable($request);
        }

        $stats = [
            'total_payouts' => Payout::count(),
            'total_paid' => Payout::where('payout_status', 'completed')->sum('amount'),
            'pending_amount' => Payout::where('payout_status', 'processing')->sum('amount'),
            'pending_count' => Payout::where('payout_status', 'processing')->count(),
        ];

        return view('admin.billing.payouts.index', compact('stats'));
    }

    public function transactions(Request $request)
    {
        if ($request->ajax()) {
            return $this->getTransactionsDataTable($request);
        }
        
        $stats = [
            'total_transactions' => Transaction::count(),
            'success_count' => Transaction::where('status', 'success')->count(),
            'total_revenue' => Transaction::where('status', 'success')->sum('amount'),
            'failed_count' => Transaction::where('status', 'failed')->count(),
        ];
        
        return view('admin.billing.transactions.index', compact('stats'));
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

    private function getInvoicesDataTable(Request $request): JsonResponse
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';
            $order = $request->get('order')[0] ?? null;
            $columns = $request->get('columns') ?? [];

            $query = Invoice::with(['client', 'trainer']);

            // Status Filter
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            // Search
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('trainer', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('client', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $totalRecords = Invoice::count();
            $filteredRecords = $query->count();

            // Sorting
            if ($order && isset($columns[$order['column']])) {
                $columnName = $columns[$order['column']]['name'];
                $direction = $order['dir'];
                
                if (in_array($columnName, ['id', 'total_amount', 'currency', 'due_date', 'status', 'created_at'])) {
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->latest();
            }

            $invoices = $query->skip($start)->take($length)->get();

            $data = $invoices->map(function ($invoice) {
                $statusBadge = match($invoice->status) {
                    'pending' => '<span class="badge bg-warning-transparent">Pending</span>',
                    'paid' => '<span class="badge bg-success-transparent">Paid</span>',
                    'failed' => '<span class="badge bg-danger-transparent">Failed</span>',
                    'cancelled' => '<span class="badge bg-secondary-transparent">Cancelled</span>',
                    default => '<span class="badge bg-info-transparent">Draft</span>'
                };

                return [
                    'id' => $invoice->id,
                    'trainer' => '<span class="fw-semibold">' . e($invoice->trainer->name ?? '#') . '</span>',
                    'client' => '<span class="fw-semibold">' . e($invoice->client->name ?? '#') . '</span>',
                    'total' => number_format($invoice->total_amount, 2),
                    'currency' => strtoupper($invoice->currency),
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—',
                    'status' => $statusBadge,
                    'created_at' => $invoice->created_at->format('M d, Y'),
                    'actions' => '<a href="' . route('admin.invoices.show', $invoice->id) . '" class="btn btn-sm btn-info-transparent"><i class="ri-eye-line"></i></a>' // Assuming show route exists or we add one
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error in BillingController@getInvoicesDataTable: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getPayoutsDataTable(Request $request): JsonResponse
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';
            $order = $request->get('order')[0] ?? null;
            $columns = $request->get('columns') ?? [];

            $query = Payout::with(['trainer']);

            if ($request->filled('status')) {
                $query->where('payout_status', $request->input('status'));
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('trainer', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $totalRecords = Payout::count();
            $filteredRecords = $query->count();

            if ($order && isset($columns[$order['column']])) {
                $columnName = $columns[$order['column']]['name'];
                $direction = $order['dir'];
                if (in_array($columnName, ['id', 'amount', 'currency', 'payout_status', 'created_at'])) {
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->latest();
            }

            $payouts = $query->skip($start)->take($length)->get();

            $data = $payouts->map(function ($payout) {
                $statusBadge = match($payout->payout_status) {
                    'completed' => '<span class="badge bg-success-transparent">Completed</span>',
                    'processing' => '<span class="badge bg-warning-transparent">Processing</span>',
                    'failed' => '<span class="badge bg-danger-transparent">Failed</span>',
                    default => '<span class="badge bg-secondary-transparent">' . ucfirst($payout->payout_status) . '</span>'
                };

                $actions = '';
                if ($payout->payout_status !== 'completed') {
                    $actions = '<button type="button" class="btn btn-sm btn-primary-transparent" onclick="confirmPayout(\'' . $payout->id . '\')">
                                    <i class="ri-bank-card-line me-1"></i> Process
                                </button>
                                <form id="payout-form-' . $payout->id . '" action="' . route('admin.payouts.process', $payout->id) . '" method="POST" style="display: none;">' . 
                               csrf_field() . 
                               '</form>';
                } else {
                    $actions = '<span class="text-success"><i class="ri-check-double-line"></i> Paid</span>';
                }

                return [
                    'id' => $payout->id,
                    'trainer' => '<span class="fw-semibold">' . e($payout->trainer->name ?? 'N/A') . '</span>',
                    'amount' => number_format($payout->amount, 2),
                    'currency' => strtoupper($payout->currency),
                    'fee' => number_format($payout->fee_amount, 2),
                    'status' => $statusBadge,
                    'scheduled' => $payout->scheduled_at ? $payout->scheduled_at->format('M d, Y H:i') : '—',
                    'created_at' => $payout->created_at->format('M d, Y'),
                    'actions' => $actions
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error in BillingController@getPayoutsDataTable: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getTransactionsDataTable(Request $request): JsonResponse
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';
            $order = $request->get('order')[0] ?? null;
            $columns = $request->get('columns') ?? [];

            $query = Transaction::with(['invoice.client', 'invoice.trainer', 'gateway']);

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                      ->orWhereHas('invoice.client', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $totalRecords = Transaction::count();
            $filteredRecords = $query->count();

            if ($order && isset($columns[$order['column']])) {
                $columnName = $columns[$order['column']]['name'];
                $direction = $order['dir'];
                if (in_array($columnName, ['id', 'transaction_id', 'amount', 'currency', 'status', 'created_at'])) {
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->latest();
            }

            $transactions = $query->skip($start)->take($length)->get();

            $data = $transactions->map(function ($transaction) {
                $statusBadge = match($transaction->status) {
                    'success' => '<span class="badge bg-success-transparent">Success</span>',
                    'failed' => '<span class="badge bg-danger-transparent">Failed</span>',
                    'refunded' => '<span class="badge bg-info-transparent">Refunded</span>',
                    default => '<span class="badge bg-secondary-transparent">' . ucfirst($transaction->status) . '</span>'
                };

                // Gateway HTML
                $gatewayHtml = match(strtolower($transaction->gateway->type ?? '')) {
                    'stripe' => '<div class="d-flex align-items-center"><i class="ri-visa-line text-primary fs-18 me-1"></i> Stripe</div>',
                    'paypal' => '<div class="d-flex align-items-center"><i class="ri-paypal-line text-info fs-18 me-1"></i> PayPal</div>',
                    default => e($transaction->gateway->name ?? 'N/A')
                };

                // User HTML
                $user = $transaction->invoice->client ?? $transaction->invoice->trainer ?? null;
                $userHtml = '';
                if ($user) {
                    $avatar = $user->profile_image 
                        ? '<img src="' . asset('storage/' . $user->profile_image) . '" alt="img" class="rounded-circle">'
                        : '<span class="avatar-initial rounded-circle bg-primary-transparent">' . substr($user->name, 0, 1) . '</span>';
                    
                    $userHtml = '<div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">' . $avatar . '</div>
                                    <div>
                                        <div class="fw-semibold">' . e($user->name) . '</div>
                                        <span class="text-muted fs-12">' . ucfirst($user->role) . '</span>
                                    </div>
                                </div>';
                } else {
                    $userHtml = '<span class="text-muted">Unknown User</span>';
                }

                // Actions
                $actions = '<div class="hstack gap-2 fs-15">
                                <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-primary-light rounded-pill" data-bs-toggle="tooltip" title="View Details">
                                    <i class="ri-eye-line"></i>
                                </a>';
                
                if ($transaction->status === 'success') {
                    $actions .= '<button type="button" class="btn btn-sm btn-icon btn-danger-light rounded-pill ms-1" data-bs-toggle="tooltip" title="Refund" onclick="confirmRefund(\'' . $transaction->id . '\')">
                                    <i class="ri-refund-line"></i>
                                </button>
                                <form id="refund-form-' . $transaction->id . '" action="' . route('admin.transactions.refund', $transaction->id) . '" method="POST" style="display: none;">
                                    ' . csrf_field() . '
                                </form>';
                }
                $actions .= '</div>';

                return [
                    'id' => '#' . ($transaction->transaction_id ?? $transaction->id),
                    'user' => $userHtml,
                    'description' => 'Payment for Invoice #' . ($transaction->invoice->invoice_number ?? 'N/A'),
                    'amount' => '<span class="fw-semibold text-success">' . $transaction->currency . ' ' . number_format($transaction->amount, 2) . '</span>',
                    'gateway' => $gatewayHtml,
                    'status' => $statusBadge,
                    'created_at' => $transaction->created_at->format('d M Y, h:i A'),
                    'actions' => $actions
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error in BillingController@getTransactionsDataTable: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
