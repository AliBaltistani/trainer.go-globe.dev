@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <x-tables.card title='Videos for "{{ $workout->name }}"'>
            <x-slot:tools>
                <div class="d-flex gap-2">
                    <a href="{{ route('workouts.show', $workout->id) }}" class="btn btn-sm btn-secondary">
                        <i class="ri-arrow-left-line"></i> Back to Workout
                    </a>
                    <a href="{{ route('workout-videos.create', $workout->id) }}" class="btn btn-sm btn-primary">
                        <i class="ri-add-line"></i> Add Video
                    </a>
                </div>
            </x-slot:tools>

            <div class="card-body p-0">
                @if($videos->count() > 0)
                    <x-tables.table 
                        :headers="['Order', 'Title', 'Type', 'Duration', 'Preview', 'Created', 'Actions']"
                        :striped="true"
                    >
                        <tbody>
                            @foreach($videos as $video)
                            <tr>
                                <td>{{ $video->order }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($video->thumbnail)
                                            <img src="{{ Storage::url($video->thumbnail) }}" alt="Thumbnail" class="rounded me-2" width="40" height="30" style="object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 30px;">
                                                <i class="ri-video-line text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $video->title }}</strong>
                                            @if($video->description)
                                                <br><small class="text-muted">{{ Str::limit($video->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $video->video_type === 'youtube' ? 'danger' : ($video->video_type === 'vimeo' ? 'info' : ($video->video_type === 'file' ? 'success' : 'primary')) }}-transparent">
                                        {{ ucfirst($video->video_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($video->duration)
                                        {{ gmdate('H:i:s', $video->duration) }}
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if($video->is_preview)
                                        <span class="badge bg-warning-transparent">Preview</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td>{{ $video->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <x-tables.actions 
                                        edit="{{ route('workout-videos.edit', [$workout->id, $video->id]) }}"
                                        delete="confirmDelete('delete-form-{{ $video->id }}')"
                                    >
                                        <form method="POST" action="{{ route('workout-videos.destroy', [$workout->id, $video->id]) }}" class="d-none" id="delete-form-{{ $video->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </x-tables.actions>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </x-tables.table>
                    
                    @if($videos->count() > 1)
                        <div class="mt-3 p-3">
                            <a href="{{ route('workout-videos.reorder-form', $workout->id) }}" class="btn btn-outline-secondary">
                                <i class="ri-drag-move-line"></i> Reorder Videos
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="ri-video-line display-4 text-muted"></i>
                        <h5 class="mt-3 text-muted">No Videos Added</h5>
                        <p class="text-muted">This workout doesn't have any videos yet.</p>
                        <a href="{{ route('workout-videos.create', $workout->id) }}" class="btn btn-primary">
                            <i class="ri-add-line"></i> Add First Video
                        </a>
                    </div>
                @endif
            </div>
        </x-tables.card>
    </div>
</div>
@endsection

@section('scripts')
    <script>
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