<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Payout::with(['trainer'])->where('trainer_id', Auth::id());
        $status = (string) $request->query('status', '');
        if ($status !== '') {
            $query->where('payout_status', $status);
        }
        $payouts = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('trainer.billing.payouts.index', compact('payouts'));
    }

    public function export(Request $request)
    {
        $query = Payout::where('trainer_id', Auth::id());
        $status = (string) $request->query('status', '');
        if ($status !== '') {
            $query->where('payout_status', $status);
        }
        $payouts = $query->orderBy('created_at', 'desc')->get();

        $response = new StreamedResponse(function () use ($payouts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Trainer', 'Amount', 'Currency', 'Fee', 'Status', 'Scheduled', 'Created']);
            foreach ($payouts as $p) {
                fputcsv($handle, [
                    $p->id,
                    optional($p->trainer)->name,
                    number_format((float) $p->amount, 2, '.', ''),
                    strtoupper((string) $p->currency),
                    number_format((float) $p->fee_amount, 2, '.', ''),
                    (string) $p->payout_status,
                    $p->scheduled_at ? $p->scheduled_at->format('Y-m-d H:i:s') : '',
                    $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }
            fclose($handle);
        });
        $filename = 'trainer_payouts_' . now()->format('Ymd_His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        return $response;
    }
}

