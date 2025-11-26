@extends('layouts.master')

@section('styles')
<style>
.spec-chip{display:inline-flex;align-items:center;padding:.375rem .5rem;border:1px solid #dee2e6;border-radius:1.5rem;margin:.25rem .5rem .25rem 0;background-color:#f8f9fa}
.spec-chip .name{margin-right:.5rem;font-weight:500}
.spec-chip .remove{line-height:1;border:none;background:transparent;color:#dc3545}
</style>
@endsection

@section('content')
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">My Specializations</h1>
        <div>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Specializations</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Current Specializations</div>
            </div>
            <div class="card-body">
                <div id="currentSpecializations" class="d-flex flex-wrap">
                    @forelse($currentSpecializations as $spec)
                        <div class="spec-chip" id="spec-{{ $spec->id }}">
                            <span class="name">{{ $spec->name }}</span>
                            <button class="remove" data-id="{{ $spec->id }}" aria-label="Remove"><i class="ri-close-line"></i></button>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No specializations added.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Add Specialization</div>
            </div>
            <div class="card-body">
                <form id="addSpecForm">
                    @csrf
                    <div class="mb-3">
                        <select class="form-select" id="specialization_id" name="specialization_id" required>
                            <option value="">Select specialization</option>
                            @foreach($activeSpecializations as $as)
                                <option value="{{ $as->id }}">{{ $as->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="addSpecBtn">
                        <span class="spinner-border spinner-border-sm me-2" id="addSpecSpinner" style="display:none"></span>
                        Add
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ri-check-line me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@endsection

@section('scripts')
<script>

$(function(){
    var form = $('#addSpecForm');
    var select = $('#specialization_id');
    var btn = $('#addSpecBtn');
    var spinner = $('#addSpecSpinner');
    form.on('submit', function(e){
        e.preventDefault();
        var specId = select.val();
        var invalid = select.parent().find('.invalid-feedback');
        if (!specId){
            invalid.text('Please select a specialization').show();
            select.addClass('is-invalid');
            return;
        }
        invalid.text('');
        select.removeClass('is-invalid');
        $.ajax({
            url: "{{ route('trainer.specializations.store') }}",
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', specialization_id: specId },
            beforeSend: function(){ btn.prop('disabled', true); spinner.show(); },
            success: function(res){
                if (res.success){
                    var chip = $('<div class="spec-chip" id="spec-'+res.data.id+'"><span class="name">'+res.data.name+'</span><button class="remove" data-id="'+res.data.id+'" aria-label="Remove"><i class="ri-close-line"></i></button></div>');
                    $('#currentSpecializations').append(chip);
                    select.find('option[value="'+res.data.id+'"]').remove();
                    select.val('');
                    showAlert('success', res.message || 'Specialization added');
                } else {
                    showAlert('error', res.message || 'Failed to add specialization');
                }
            },
            error: function(xhr){
                var msg = 'Failed to add specialization';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showAlert('error', msg);
            },
            complete: function(){ btn.prop('disabled', false); spinner.hide(); }
        });
    });
    $(document).on('click', '.spec-chip .remove', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var button = $(this);
        var name = $('#spec-'+id+' .name').text();
        $.ajax({
            url: "{{ route('trainer.specializations.destroy', ':id') }}".replace(':id', id),
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            beforeSend: function(){ button.prop('disabled', true); },
            success: function(res){
                if (res.success){
                    $('#spec-'+id).remove();
                    if (select.find('option[value="'+id+'"]').length === 0){
                        select.append('<option value="'+id+'">'+name+'</option>');
                    }
                    showAlert('success', res.message || 'Specialization removed');
                } else {
                    showAlert('error', res.message || 'Failed to remove specialization');
                }
            },
            error: function(xhr){
                var msg = 'Failed to remove specialization';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showAlert('error', msg);
            },
            complete: function(){ button.prop('disabled', false); }
        });
    });
    function showAlert(type, message){
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var iconClass = type === 'success' ? 'ri-check-line' : 'ri-error-warning-line';
        var alertHtml = '<div class="alert '+alertClass+' alert-dismissible fade show" role="alert">\n'+
                        '<i class="'+iconClass+' me-2"></i>'+message+
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\n'+
                        '</div>';
        $('.alert').remove();
        $('.page-header-breadcrumb').after(alertHtml);
        setTimeout(function(){ $('.alert').fadeOut(); }, 5000);
    }
});
</script>
@endsection
