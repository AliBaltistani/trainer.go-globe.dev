<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\TrainerSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $trainerId = Auth::id();
        $status = (string) $request->query('status', '');
        $query = TrainerSubscription::with(['client:id,name,email'])
            ->where('trainer_id', $trainerId)
            ->orderByDesc('subscribed_at');
        if ($status !== '') {
            $query->where('status', $status);
        }
        $subscriptions = $query->paginate(20);
        return view('trainer.subscriptions.index', compact('subscriptions', 'status'));
    }

    public function destroy($id)
    {
        $trainerId = Auth::id();
        $subscription = TrainerSubscription::where('trainer_id', $trainerId)->findOrFail($id);
        $subscription->update([
            'status' => 'inactive',
            'unsubscribed_at' => now(),
        ]);
        return redirect()->route('trainer.subscriptions.index');
    }
}

