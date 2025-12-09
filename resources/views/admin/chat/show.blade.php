@extends('layouts.master')

@section('title', 'View Conversation')

@section('content')
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-18 mb-0">Conversation Details</h1>
        <p class="text-muted mb-0">Trainer: {{ $conversation->trainer->name }} | Client: {{ $conversation->client->name }}</p>
    </div>
    <div>
        <a href="{{ route('admin.chat.index') }}" class="btn btn-primary-light">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="chat-content" style="height: 600px; overflow-y: auto;">
                    <ul class="list-unstyled">
                        @foreach($conversation->messages as $message)
                            @php
                                $isTrainer = $message->sender_id == $conversation->trainer_id;
                                $senderName = $isTrainer ? $conversation->trainer->name : $conversation->client->name;
                                $alignClass = $isTrainer ? 'text-end' : 'text-start';
                                $bgClass = $isTrainer ? 'bg-primary text-fixed-white' : 'bg-light text-default';
                                $bubbleAlign = $isTrainer ? 'justify-content-end' : 'justify-content-start';
                            @endphp
                            <li class="mb-3">
                                <div class="d-flex {{ $bubbleAlign }}">
                                    <div class="d-flex flex-column" style="max-width: 70%;">
                                        <span class="fs-11 text-muted mb-1 {{ $alignClass }}">{{ $senderName }}</span>
                                        <div class="p-3 rounded {{ $bgClass }}">
                                            @if($message->message_type == 'text')
                                                <p class="mb-0">{{ $message->message }}</p>
                                            @elseif($message->message_type == 'image')
                                                <img src="{{ asset($message->media_url) }}" class="img-fluid rounded" style="max-width: 200px;">
                                            @else
                                                <a href="{{ asset($message->media_url) }}" target="_blank" class="{{ $isTrainer ? 'text-fixed-white' : '' }}">Download File</a>
                                            @endif
                                        </div>
                                        <span class="fs-11 text-muted mt-1 {{ $alignClass }}">{{ $message->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
