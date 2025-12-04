@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <x-tables.card title='Exercises for "{{ $workout->name }}"'>
            <x-slot:tools>
                <div class="d-flex gap-2">
                    <a href="{{ route('workouts.show', $workout->id) }}" class="btn btn-sm btn-primary-light">
                        <i class="ri-arrow-left-line"></i> Back to Workout
                    </a>
                    <a href="{{ route('workout-exercises.create', $workout->id) }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-line"></i> Add Exercise
                    </a>
                </div>
            </x-slot:tools>

            <div class="card-body p-0">
                @if($exercises->count() > 0)
                    <x-tables.table 
                        id="exercisesTable"
                        :headers="['Order', 'Exercise', 'Sets', 'Reps', 'Weight (lbs)', 'Duration', 'Rest', 'Tempo', 'Status', 'Actions']"
                        :bordered="true"
                    >
                        <tbody>
                            @foreach($exercises as $exercise)
                                <tr data-id="{{ $exercise->id }}">
                                    <td>
                                        <span class="badge bg-primary-transparent">{{ $exercise->order }}</span>
                                        <i class="ri-drag-move-2-line ms-2 text-muted" style="cursor: move;"></i>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $exercise->exercise->name ?? 'Exercise #' . $exercise->id }}</div>
                                        @if($exercise->notes)
                                            <small class="text-muted">{{ Str::limit($exercise->notes, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $exercise->sets ?? '-' }}</td>
                                    <td>{{ $exercise->reps ?? '-' }}</td>
                                    <td>{{ $exercise->formatted_weight ?? '-' }}</td>
                                    <td>{{ $exercise->duration ? $exercise->duration . 's' : '-' }}</td>
                                    <td>{{ $exercise->rest_interval ? $exercise->rest_interval . 's' : '-' }}</td>
                                    <td>{{ $exercise->tempo ?? '-' }}</td>
                                    <td>
                                        @if($exercise->is_active)
                                            <span class="badge bg-success-transparent">Active</span>
                                        @else
                                            <span class="badge bg-light text-dark">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-tables.actions 
                                            view="{{ route('workout-exercises.show', [$workout->id, $exercise->id]) }}"
                                            edit="{{ route('workout-exercises.edit', [$workout->id, $exercise->id]) }}"
                                            delete="confirmDelete('delete-form-{{ $exercise->id }}')"
                                        >
                                            <form action="{{ route('workout-exercises.destroy', [$workout->id, $exercise->id]) }}" method="POST" class="d-none" id="delete-form-{{ $exercise->id }}">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </x-tables.actions>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-tables.table>
                    
                    <!-- Pagination -->
                    @if($exercises->hasPages())
                        <div class="d-flex justify-content-center mt-4 pb-3">
                            {{ $exercises->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="ri-fitness-line" style="font-size: 3rem; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">No Exercises Found</h5>
                        <p class="text-muted">This workout doesn't have any exercises yet.</p>
                        <a href="{{ route('workout-exercises.create', $workout->id) }}" class="btn btn-primary">
                            <i class="ri-add-line"></i> Add First Exercise
                        </a>
                    </div>
                @endif
            </div>
        </x-tables.card>
    </div>
</div>

<!-- Workout Summary Card -->
<div class="row mt-4">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Workout Summary</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="mb-1">{{ $exercises->count() }}</h4>
                            <p class="text-muted mb-0">Total Exercises</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="mb-1">{{ $exercises->where('is_active', 1)->count() }}</h4>
                            <p class="text-muted mb-0">Active Exercises</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="mb-1">{{ $exercises->sum('sets') ?: 0 }}</h4>
                            <p class="text-muted mb-0">Total Sets</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="mb-1">{{ $workout->formatted_duration }}</h4>
                            <p class="text-muted mb-0">Workout Duration</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable for exercise reordering
    const sortableElement = document.querySelector('#exercisesTable tbody');
    if (sortableElement) {
        const sortable = Sortable.create(sortableElement, {
            handle: '.ri-drag-move-2-line',
            animation: 150,
            onEnd: function(evt) {
                const exerciseIds = Array.from(sortableElement.children).map(row => row.dataset.id);
                
                // Send AJAX request to update order
                fetch(`{{ route('workout-exercises.reorder', $workout->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        exercise_ids: exerciseIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update order badges
                        sortableElement.querySelectorAll('tr').forEach((row, index) => {
                            const badge = row.querySelector('.badge');
                            if (badge) {
                                badge.textContent = index + 1;
                            }
                        });
                        
                        // Show success message
                        showToast('Exercise order updated successfully!', 'success');
                    } else {
                        showToast('Failed to update exercise order', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while updating exercise order', 'error');
                });
            }
        });
    }
});

function showToast(message, type = 'info') {
    // Simple toast notification (you can replace with your preferred toast library)
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}

function confirmDelete(formId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>
@endsection