@extends('layouts.master')

@section('title', 'Chat Conversations')

@section('content')
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-semibold fs-18 mb-0">Chat Conversations</h1>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table text-nowrap table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Trainer</th>
                                <th scope="col">Client</th>
                                <th scope="col">Last Message</th>
                                <th scope="col">Updated At</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($conversations as $conversation)
                                <tr>
                                    <td>{{ $conversation->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2 avatar-rounded">
                                                <img src="{{ $conversation->trainer->profile_image ? asset($conversation->trainer->profile_image) : asset('build/assets/images/faces/9.jpg') }}" alt="img">
                                            </span>
                                            {{ $conversation->trainer->name }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2 avatar-rounded">
                                                <img src="{{ $conversation->client->profile_image ? asset($conversation->client->profile_image) : asset('build/assets/images/faces/9.jpg') }}" alt="img">
                                            </span>
                                            {{ $conversation->client->name }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($conversation->lastMessage)
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                                {{ Str::limit($conversation->lastMessage->message, 50) }}
                                            </span>
                                        @else
                                            <span class="text-muted">No messages</span>
                                        @endif
                                    </td>
                                    <td>{{ $conversation->updated_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('admin.chat.show', $conversation->id) }}" class="btn btn-icon btn-sm btn-info-light">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No conversations found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    {{ $conversations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
