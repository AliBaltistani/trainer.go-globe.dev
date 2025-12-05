<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $stats = [
            'total_gateways' => PaymentGateway::count(),
            'active_gateways' => PaymentGateway::where('enabled', true)->count(),
            'default_gateway' => PaymentGateway::where('is_default', true)->value('name') ?? 'None',
        ];

        return view('admin.billing.payment-gateways.index', compact('stats'));
    }

    private function getDataTableData(Request $request): JsonResponse
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';
            $order = $request->get('order')[0] ?? null;
            $columns = $request->get('columns') ?? [];

            $query = PaymentGateway::query();

            // Search
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%");
                });
            }

            $totalRecords = PaymentGateway::count();
            $filteredRecords = $query->count();

            // Sorting
            if ($order && isset($columns[$order['column']])) {
                $columnName = $columns[$order['column']]['name'];
                $direction = $order['dir'];
                if (in_array($columnName, ['id', 'name', 'type'])) {
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderByDesc('is_default')->orderBy('name');
            }

            $gateways = $query->skip($start)->take($length)->get();

            $data = $gateways->map(function ($gateway) {
                return [
                    'id' => $gateway->id,
                    'name' => '<span class="fw-semibold">' . e($gateway->name) . '</span>',
                    'type' => $gateway->type === 'stripe' 
                        ? '<span class="badge bg-primary-transparent">Stripe</span>' 
                        : '<span class="badge bg-info-transparent">PayPal</span>',
                    'status' => $gateway->enabled 
                        ? '<span class="badge bg-success-transparent">Enabled</span>' 
                        : '<span class="badge bg-warning-transparent">Disabled</span>',
                    'default' => $gateway->is_default 
                        ? '<span class="badge bg-success">Default</span>' 
                        : '<span class="badge bg-secondary-transparent">—</span>',
                    'actions' => $this->getActionButtons($gateway)
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error in PaymentGatewayController@getDataTableData: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load gateways data'], 500);
        }
    }

    private function getActionButtons($gateway)
    {
        $buttons = '<div class="hstack gap-2 fs-15">';
        
        // Enable/Disable Toggle
        $buttons .= '<form action="' . route('admin.payment-gateways.enable', $gateway->id) . '" method="POST" class="d-inline">';
        $buttons .= csrf_field();
        $icon = $gateway->enabled ? 'ri-pause-line' : 'ri-play-line';
        $btnClass = $gateway->enabled ? 'btn-warning-transparent' : 'btn-success-transparent';
        $title = $gateway->enabled ? 'Disable' : 'Enable';
        $val = $gateway->enabled ? 0 : 1;
        $buttons .= '<button class="btn btn-icon btn-sm ' . $btnClass . ' rounded-pill" title="' . $title . '">';
        $buttons .= '<i class="' . $icon . '"></i>';
        $buttons .= '</button>';
        $buttons .= '<input type="hidden" name="enabled" value="' . $val . '">';
        $buttons .= '</form>';

        // Set Default
        $buttons .= '<form action="' . route('admin.payment-gateways.set-default', $gateway->id) . '" method="POST" class="d-inline">';
        $buttons .= csrf_field();
        $buttons .= '<button class="btn btn-icon btn-sm btn-primary-transparent rounded-pill" title="Set Default">';
        $buttons .= '<i class="ri-star-line"></i>';
        $buttons .= '</button>';
        $buttons .= '</form>';

        // Edit
        $editData = [
            'id' => $gateway->id,
            'name' => $gateway->name,
            'type' => $gateway->type,
            'public_key' => $gateway->public_key,
            'secret_key' => $gateway->secret_key,
            'webhook_secret' => $gateway->webhook_secret,
            'connect_client_id' => $gateway->connect_client_id,
        ];
        $json = htmlspecialchars(json_encode($editData), ENT_QUOTES, 'UTF-8');
        
        $buttons .= '<button class="btn btn-icon btn-sm btn-info-transparent rounded-pill" onclick=\'editGateway(' . $json . ')\' title="Edit">';
        $buttons .= '<i class="ri-edit-line"></i>';
        $buttons .= '</button>';

        $buttons .= '</div>';

        return $buttons;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:stripe,paypal',
            'public_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'connect_client_id' => 'nullable|string',
            'enabled' => 'sometimes|boolean',
        ]);

        $gateway = PaymentGateway::create($data);
        return redirect()->route('admin.payment-gateways.index')->with('success', 'Gateway created');
    }

    public function update($id, Request $request)
    {
        $gateway = PaymentGateway::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'public_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'connect_client_id' => 'nullable|string',
            'enabled' => 'sometimes|boolean',
        ]);
        $gateway->update($data);
        return redirect()->route('admin.payment-gateways.index')->with('success', 'Gateway updated');
    }

    public function enable($id, Request $request)
    {
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->enabled = (bool) $request->input('enabled', true);
        $gateway->save();
        return redirect()->route('admin.payment-gateways.index')->with('success', 'Gateway status updated');
    }

    public function setDefault($id)
    {
        PaymentGateway::query()->update(['is_default' => false]);
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->is_default = true;
        $gateway->enabled = true;
        $gateway->save();
        return redirect()->route('admin.payment-gateways.index')->with('success', 'Default gateway set');
    }
}

