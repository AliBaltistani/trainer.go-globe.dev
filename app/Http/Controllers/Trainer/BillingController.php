<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payout;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function dashboard()
    {
        $trainerId = Auth::id();
        $revenue = (float) Invoice::where('trainer_id', $trainerId)->where('status', 'paid')->sum('total_amount');
        $pendingPayouts = (float) Payout::where('trainer_id', $trainerId)->where('payout_status', 'processing')->sum('amount');
        $feesCollected = (float) Payout::where('trainer_id', $trainerId)->sum('fee_amount');
        $totals = [
            'revenue' => $revenue,
            'pending_payouts' => $pendingPayouts,
            'fees_collected' => $feesCollected,
        ];
        return view('trainer.billing.dashboard', compact('totals'));
    }
}

