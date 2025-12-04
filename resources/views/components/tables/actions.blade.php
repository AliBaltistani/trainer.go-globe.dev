@props(['view' => null, 'edit' => null, 'delete' => null, 'toggle' => null])

<div class="hstack gap-2 fs-15 justify-content-end">
    @if($view)
        <a href="{{ $view }}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill" title="View">
            <i class="ri-eye-line"></i>
        </a>
    @endif
    @if($edit)
        <a href="{{ $edit }}" class="btn btn-icon btn-sm btn-primary-transparent rounded-pill" title="Edit">
            <i class="ri-edit-line"></i>
        </a>
    @endif
    @if($toggle)
         <button type="button" class="btn btn-icon btn-sm btn-warning-transparent rounded-pill" onclick="{{ $toggle }}" title="Toggle Status">
            <i class="ri-toggle-line"></i>
        </button>
    @endif
    @if($delete)
        <button type="button" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill" onclick="{{ $delete }}" title="Delete">
            <i class="ri-delete-bin-line"></i>
        </button>
    @endif
    {{ $slot }}
</div>
