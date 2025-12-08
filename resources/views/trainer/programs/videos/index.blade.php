@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <x-tables.card title="Program Videos" :icon="'ri-video-line'" :iconColor="'text-primary'">
        <x-slot:tools>
            <div class="d-flex gap-2">
                 <a href="{{ route('trainer.program-builder.show', $program->id) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Program Builder
                </a>
                <a href="{{ route('trainer.program-videos.create', $program->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Video
                </a>
            </div>
        </x-slot:tools>
        
        <div class="card-body p-0">
            @if($videos->count() > 0)
                <x-tables.table 
                    :headers="['Order', 'Title', 'Type', 'Duration', 'Preview', 'Created', 'Actions']"
                    :bordered="true"
                >
                    <tbody>
                        @foreach($videos as $video)
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $video->order }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($video->thumbnail)
                                        <img src="{{ Storage::url($video->thumbnail) }}" alt="Thumbnail" class="rounded me-2" width="40" height="40" style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-video text-muted"></i>
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
                                    <small class="text-muted">{{ $video->formatted_duration }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($video->is_preview)
                                    <span class="badge bg-warning">Yes</span>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                            <td>{{ $video->created_at->format('d/m/Y') }}</td>
                            <td>
                                <x-tables.actions 
                                    edit="{{ route('trainer.program-videos.edit', [$program->id, $video->id]) }}"
                                    delete="confirmDelete('delete-form-{{ $video->id }}')"
                                >
                                    <form method="POST" action="{{ route('trainer.program-videos.destroy', [$program->id, $video->id]) }}" class="d-none" id="delete-form-{{ $video->id }}">
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
                <div class="mt-3 p-3 border-top">
                    <a href="{{ route('trainer.program-videos.reorder-form', $program->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrows-alt me-2"></i>Reorder Videos
                    </a>
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-video display-4 text-muted"></i>
                    <h5 class="mt-3 text-muted">No Videos Added</h5>
                    <p class="text-muted mb-3">This program doesn't have any videos yet.</p>
                    <a href="{{ route('trainer.program-videos.create', $program->id) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add First Video
                    </a>
                </div>
            @endif
        </div>
    </x-tables.card>
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
