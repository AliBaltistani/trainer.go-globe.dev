@extends('layouts.master')

@section('content')
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h1 class="page-title fw-semibold fs-18 mb-0">Create Invoice</h1>
            <div>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('trainer.billing.invoices.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('trainer.billing.invoices.store') }}">
        @csrf
        <div class="card custom-card mb-3">
            <div class="card-header"><div class="card-title">Invoice Details</div></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select" id="clientSelect" required>
                            <option value="">Select client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <input type="text" name="currency" class="form-control" value="USD" maxlength="3" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Base Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="0.00" required />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">Items</div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn"><i class="ri-add-line"></i> Add Item</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th style="width:160px">Amount</th>
                                <th style="width:120px">Qty</th>
                                <th style="width:100px">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="items[0][title]" class="form-control" /></td>
                                <td><input type="number" step="0.01" name="items[0][amount]" class="form-control" /></td>
                                <td><input type="number" name="items[0][qty]" class="form-control" value="1" /></td>
                                <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end gap-4 mt-2">
                    <div><strong>Items Count:</strong> <span id="itemsCount">0</span></div>
                    <div><strong>Subtotal:</strong> <span id="itemsSubtotal">0.00</span></div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Invoice</button>
                    <a href="{{ route('trainer.billing.invoices.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    (function(){
        let idx = 1;
        const addBtn = document.getElementById('addItemBtn');
        const tbody = document.querySelector('#itemsTable tbody');
        const clientSelect = document.getElementById('clientSelect');
        const itemsCountEl = document.getElementById('itemsCount');
        const itemsSubtotalEl = document.getElementById('itemsSubtotal');

        function recalc(){
            let rows = tbody.querySelectorAll('tr');
            let subtotal = 0.0;
            rows.forEach(function(row){
                const amt = parseFloat((row.querySelector('input[name*="[amount]"]')?.value || '0')) || 0;
                const qty = parseInt((row.querySelector('input[name*="[qty]"]')?.value || '1')) || 1;
                subtotal += amt * qty;
            });
            itemsCountEl.textContent = String(rows.length);
            itemsSubtotalEl.textContent = subtotal.toFixed(2);
        }

        async function loadClientItems(clientId){
            if(!clientId){ return; }
            try{
                const resp = await fetch(`{{ route('trainer.billing.invoices.client-items', ['clientId' => '__CID__']) }}`.replace('__CID__', clientId));
                const data = await resp.json();
                if(data && data.success){
                    tbody.innerHTML = '';
                    idx = 0;
                    data.items.forEach(function(it){
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><input type="text" name="items[${idx}][title]" class="form-control" value="${it.title || ''}" /></td>
                            <td><input type="number" step="0.01" name="items[${idx}][amount]" class="form-control" value="${Number(it.amount).toFixed(2)}" /></td>
                            <td><input type="number" name="items[${idx}][qty]" class="form-control" value="${it.qty || 1}" /></td>
                            <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">Remove</button></td>
                        `;
                        tbody.appendChild(tr);
                        idx++;
                    });
                    recalc();
                }
            } catch(e){ console.error(e); }
        }

        clientSelect.addEventListener('change', function(){
            loadClientItems(this.value);
        });

        addBtn.addEventListener('click', function(){
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="items[${idx}][title]" class="form-control" required /></td>
                <td><input type="number" step="0.01" name="items[${idx}][amount]" class="form-control" required /></td>
                <td><input type="number" name="items[${idx}][qty]" class="form-control" value="1" required /></td>
                <td><button type="button" class="btn btn-sm btn-danger removeItemBtn">Remove</button></td>
            `;
            tbody.appendChild(tr);
            idx++;
            recalc();
        });
        tbody.addEventListener('click', function(e){
            if(e.target && e.target.classList.contains('removeItemBtn')){
                const tr = e.target.closest('tr');
                tr && tr.remove();
                recalc();
            }
        });
        tbody.addEventListener('input', function(e){
            if(e.target && (e.target.name.includes('[amount]') || e.target.name.includes('[qty]'))){
                recalc();
            }
        });
        recalc();
    })();
</script>
@endsection
