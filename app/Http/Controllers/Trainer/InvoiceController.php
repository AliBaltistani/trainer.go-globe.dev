<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Schedule;
use App\Models\Workout;
use App\Models\TrainerSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['trainer', 'client'])->where('trainer_id', Auth::id());
        $status = (string) $request->query('status', '');
        if ($status !== '') {
            $query->where('status', $status);
        }
        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('trainer.billing.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $trainerId = Auth::id();
        $subs = TrainerSubscription::active()->where('trainer_id', $trainerId)->with('client')->get();
        $clients = $subs->pluck('client')->filter()->sortBy('name')->values();
        return view('trainer.billing.invoices.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $trainerId = Auth::id();
        $data = $request->validate([
            'client_id' => 'required|integer|exists:users,id',
            'currency' => 'nullable|string|size:3',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'required|string',
            'items' => 'nullable|array',
            'items.*.workout_id' => 'nullable|integer|exists:workouts,id',
            'items.*.title' => 'nullable|string',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.qty' => 'nullable|integer|min:1',
        ]);

        $total = isset($data['amount']) ? (float) $data['amount'] : 0.0;

        $invoice = Invoice::create([
            'trainer_id' => $trainerId,
            'client_id' => (int) $data['client_id'],
            'currency' => ($data['currency'] ?? 'USD'),
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'created_by' => 'trainer',
        ]);

        if (!empty($data['items'])) {
            foreach ((array) $data['items'] as $it) {
                $title = $it['title'] ?? null;
                if (!$title && !empty($it['workout_id'])) {
                    $w = Workout::find($it['workout_id']);
                    $title = $w ? $w->name : 'Workout';
                }
                $qty = $it['qty'] ?? 1;
                $amount = (float) ($it['amount'] ?? 0);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'workout_id' => $it['workout_id'] ?? null,
                    'title' => (string) ($title ?? 'Item'),
                    'amount' => $amount,
                    'qty' => (int) $qty,
                ]);
                $total += $amount * $qty;
            }
        } else {
            $workouts = Workout::where('user_id', $trainerId)
                ->whereHas('assignments', function ($q) use ($data) {
                    $q->where('assigned_to', $data['client_id'] ?? null)
                      ->where('assigned_to_type', 'client')
                      ->whereIn('status', ['assigned', 'in_progress']);
                })
                ->get();

            foreach ($workouts as $w) {
                $price = (float) ($w->price ?? 0);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'workout_id' => $w->id,
                    'title' => $w->name,
                    'amount' => $price,
                    'qty' => 1,
                ]);
                $total += $price;
            }
        }

        $invoice->update(['total_amount' => $total]);

        return redirect()->route('trainer.billing.invoices.show', $invoice->id);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['trainer', 'client', 'items'])->where('trainer_id', Auth::id())->findOrFail($id);
        return view('trainer.billing.invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $trainerId = Auth::id();
        $invoice = Invoice::with('items')->where('trainer_id', $trainerId)->findOrFail($id);
        $subs = TrainerSubscription::active()->where('trainer_id', $trainerId)->with('client')->get();
        $clients = $subs->pluck('client')->filter()->sortBy('name')->values();
        return view('trainer.billing.invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $trainerId = Auth::id();
        $invoice = Invoice::where('trainer_id', $trainerId)->findOrFail($id);
        $data = $request->validate([
            'client_id' => 'required|integer|exists:users,id',
            'currency' => 'nullable|string|size:3',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'required|string',
            'status' => 'required|string|in:draft,pending,paid,failed,cancelled',
            'items' => 'nullable|array',
            'items.*.workout_id' => 'nullable|integer|exists:workouts,id',
            'items.*.title' => 'nullable|string',
            'items.*.amount' => 'nullable|numeric|min:0',
            'items.*.qty' => 'nullable|integer|min:1',
        ]);

        $total = isset($data['amount']) ? (float) $data['amount'] : 0.0;

        $invoice->update([
            'client_id' => (int) $data['client_id'],
            'currency' => ($data['currency'] ?? 'USD'),
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => (string) $data['status'],
        ]);

        $invoice->items()->delete();
        if (!empty($data['items'])) {
            foreach ((array) $data['items'] as $it) {
                $title = $it['title'] ?? null;
                if (!$title && !empty($it['workout_id'])) {
                    $w = Workout::find($it['workout_id']);
                    $title = $w ? $w->name : 'Workout';
                }
                $qty = $it['qty'] ?? 1;
                $amount = (float) ($it['amount'] ?? 0);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'workout_id' => $it['workout_id'] ?? null,
                    'title' => (string) ($title ?? 'Item'),
                    'amount' => $amount,
                    'qty' => (int) $qty,
                ]);
                $total += $amount * $qty;
            }
        } else {
            $workouts = Workout::where('user_id', $trainerId)
                ->whereHas('assignments', function ($q) use ($data) {
                    $q->where('assigned_to', $data['client_id'] ?? null)
                      ->where('assigned_to_type', 'client')
                      ->whereIn('status', ['assigned', 'in_progress']);
                })
                ->get();

            foreach ($workouts as $w) {
                $price = (float) ($w->price ?? 0);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'workout_id' => $w->id,
                    'title' => $w->name,
                    'amount' => $price,
                    'qty' => 1,
                ]);
                $total += $price;
            }
        }

        $invoice->update(['total_amount' => $total]);

        return redirect()->route('trainer.billing.invoices.show', $invoice->id);
    }

    public function destroy($id)
    {
        $trainerId = Auth::id();
        $invoice = Invoice::where('trainer_id', $trainerId)->findOrFail($id);
        $invoice->items()->delete();
        $invoice->delete();
        return redirect()->route('trainer.billing.invoices.index');
    }

    public function clientItems(int $clientId)
    {
        $trainerId = Auth::id();

        $workouts = Workout::where('user_id', $trainerId)
            ->whereHas('assignments', function ($q) use ($clientId) {
                $q->where('assigned_to', $clientId)
                  ->where('assigned_to_type', 'client')
                  ->whereIn('status', ['assigned', 'in_progress']);
            })
            ->with('assignments')
            ->get();

        $workoutItems = $workouts->map(function ($w) {
            return [
                'type' => 'workout',
                'workout_id' => $w->id,
                'title' => $w->name,
                'amount' => (float) ($w->price ?? 0),
                'qty' => 1,
                'metadata' => [
                    'formatted_price' => $w->formatted_price,
                    'duration' => $w->duration,
                ],
            ];
        })->values();

        $sessions = Schedule::query()
            ->where('trainer_id', $trainerId)
            ->where('client_id', $clientId)
            ->whereIn('status', [Schedule::STATUS_CONFIRMED])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $sessionItems = $sessions->map(function ($s) {
            return [
                'type' => 'session',
                'schedule_id' => $s->id,
                'title' => $s->meeting_agenda ?? 'Training Session',
                'amount' => 0.0,
                'qty' => 1,
                'metadata' => [
                    'date' => $s->date,
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'session_type' => $s->session_type,
                ],
            ];
        })->values();

        $items = $workoutItems->toBase()->merge($sessionItems->toBase())->values();
        $subtotal = array_sum(array_map(function ($it) { return (float) $it['amount'] * (int) $it['qty']; }, $items->all()));

        return response()->json([
            'success' => true,
            'items' => $items,
            'summary' => [
                'items_count' => $items->count(),
                'subtotal' => $subtotal,
            ],
        ]);
    }
}
