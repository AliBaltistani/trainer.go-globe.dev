@extends('layouts.master')

@section('styles')

        <!-- GLightbox CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/glightbox/css/glightbox.min.css')}}">
        <style>
            /* Modern Chat Styling */
            .chat-content {
                background-color: #f5f7fa;
                background-image: none !important;
            }
            .chat-content-background { display: none; }

            .chat-item {
                margin-bottom: 20px;
                display: flex;
                align-items: flex-end;
            }
            
            .chat-item-end {
                justify-content: flex-end;
            }
            
            .chat-item-start {
                justify-content: flex-start;
            }

            .chat-item-box {
                max-width: 75%;
                display: flex;
                align-items: flex-end;
            }

            .chat-item-content {
                width: 100%;
            }

            .chat-item-text {
                padding: 12px 18px;
                border-radius: 18px;
                font-size: 14px;
                line-height: 1.5;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                position: relative;
                word-wrap: break-word;
            }

            /* Received Message */
            .chat-item-start .chat-item-text {
                background-color: #ffffff;
                color: #333;
                border-bottom-left-radius: 4px;
                border: 1px solid #eef0f3;
            }

            /* Sent Message */
            .chat-item-end .chat-item-text {
                background-color: var(--primary-color, #e6533c); /* Fallback to orange if var not defined */
                color: #fff;
                border-bottom-right-radius: 4px;
            }
            .chat-item-end .chat-item-text p {
                color: #fff !important;
            }
            .chat-item-end .chat-item-text .text-muted {
                color: rgba(255,255,255,0.8) !important;
            }

            /* Metadata (Time) */
            .chat-item-meta {
                margin-top: 5px;
                font-size: 11px;
                opacity: 0.7;
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .chat-item-end .chat-item-meta {
                justify-content: flex-end;
            }

            /* Avatars in Chat */
            .chat-avatar-img {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                object-fit: cover;
                margin-bottom: 4px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .chat-item-start .chat-avatar-img {
                margin-right: 10px;
            }
            .chat-item-end .chat-avatar-img {
                margin-left: 10px;
                order: 2;
            }
            .chat-item-end .chat-item-box {
                order: 1;
            }

            /* Attachment Styling */
            .chat-attachment-card {
                display: flex;
                align-items: center;
                background: rgba(0,0,0,0.05);
                padding: 10px;
                border-radius: 10px;
                margin-top: 5px;
            }
            .chat-item-end .chat-attachment-card {
                background: rgba(255,255,255,0.15);
            }
            .chat-attachment-icon {
                font-size: 24px;
                margin-right: 10px;
            }
            .chat-attachment-info {
                flex: 1;
                overflow: hidden;
            }
            .chat-attachment-name {
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                font-size: 13px;
            }
            .chat-attachment-size {
                font-size: 11px;
                opacity: 0.8;
            }

            /* Footer/Input Area */
            .chat-footer {
                background: #fff;
                border-top: 1px solid #eee;
                padding: 15px;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.02);
            }
            .chat-message-space {
                background-color: #f0f2f5;
                border-radius: 24px !important;
                padding-left: 20px;
                padding-right: 20px;
            }
            .chat-footer .btn-icon {
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            }
            .chat-footer .btn-icon:hover {
                transform: translateY(-2px);
            }
            
            /* Fix Scrolling */
            .chat-content {
                height: calc(100vh - 280px); /* Adjust based on header/footer height */
                overflow-y: auto;
                padding: 20px;
                scrollbar-width: thin; /* Firefox */
            }
            .chat-content::-webkit-scrollbar {
                width: 6px;
            }
            .chat-content::-webkit-scrollbar-thumb {
                background-color: rgba(0,0,0,0.2);
                border-radius: 3px;
            }
            #chat-messages-list {
                margin-bottom: 0;
                padding-bottom: 10px;
            }

            /* Responsive tweaks */
            @media (max-width: 768px) {
                .chat-item-box { max-width: 85%; }
                .chat-item-text { padding: 10px 14px; }
            }
        </style>
@endsection

@section('content')
	
                    <!-- Start::page-header -->
                    <div class="page-header-breadcrumb mb-3">
                        <div class="d-flex align-center justify-content-between flex-wrap">
                            <h1 class="page-title fw-medium fs-18 mb-0">Chat</h1>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);">
                                        Pages
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Chat</li>
                            </ol>
                        </div>
                    </div>
                    <!-- End::page-header -->
                    
                    <div class="main-chart-wrapper gap-lg-2 gap-0 mb-3 d-lg-flex">
                        <div class="chat-info border">
                            <div class="d-flex align-items-center justify-content-between w-100 p-3 border-bottom"> 
                                <div> 
                                    <h5 class="fw-semibold mb-0">Messages</h5> 
                                </div> 
                                <div class="dropdown ms-2">
                                    <button aria-label="button" class="btn btn-icon btn-light btn-wave waves-light btn-sm" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#newChatModal">New Chat</a></li>
                                        <!-- <li><a class="dropdown-item" href="javascript:void(0);">Create Group</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);">Invite a Friend</a></li> -->
                                    </ul>
                                </div>
                            </div>
                            <ul class="nav nav-style-1 nav-pills nav-justified p-2 border-bottom d-flex"
                                id="myTab1" role="tablist">
                                <li class="nav-item me-0" role="presentation">
                                    <button class="nav-link active" id="users-tab" data-bs-toggle="tab"
                                        data-bs-target="#users-tab-pane" type="button" role="tab"
                                        aria-controls="users-tab-pane" aria-selected="true">Recents
                                        @if($totalUnread > 0)
                                            <span class="badge bg-secondary ms-1 rounded-pill">{{ $totalUnread }}</span>
                                        @endif
                                    </button>
                                </li>
                                <li class="nav-item me-0" role="presentation">
                                    <button class="nav-link" id="groups-tab" data-bs-toggle="tab"
                                        data-bs-target="#groups-tab-pane" type="button" role="tab"
                                        aria-controls="groups-tab-pane" aria-selected="false">Unread
                                        @if($unreadConversationsCount > 0)
                                            <span class="badge bg-secondary ms-1 rounded-pill">{{ $unreadConversationsCount }}</span>
                                        @endif
                                    </button>
                                </li>
                                <li class="nav-item me-0" role="presentation">
                                    <button class="nav-link" id="contacts-tab" data-bs-toggle="tab"
                                        data-bs-target="#contacts-tab-pane" type="button" role="tab"
                                        aria-controls="contacts-tab-pane" aria-selected="false">Contacts
                                    </button>
                                </li>
                               
                            </ul>
                            <div class="chat-search p-3 border-bottom border-block-end-dashed"> 
                                <div class="input-group"> 
                                    <input type="text" class="form-control" placeholder="Search Here" aria-describedby="button-addon2"> 
                                    <button aria-label="button" class="btn btn-primary" type="button" id="button-addon2"><i class="ri-search-line"></i></button> 
                                </div> 
                            </div>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane show active border-0 chat-users-tab" id="users-tab-pane"
                                    role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                                    <ul class="list-unstyled mb-0 mt-2 chat-users-tab" id="chat-msg-scroll">
                                        <li class="pb-0">
                                            <p class="text-muted fs-11 fw-medium mb-2 op-7">Recent Chats</p>
                                        </li>
                                        @forelse($conversations as $conversation)
                                            @php
                                                $otherUser = (Auth::id() == $conversation->trainer_id) ? $conversation->client : $conversation->trainer;
                                                $lastMsg = $conversation->lastMessage;
                                            @endphp
                                            <li class="checkforactive" data-id="{{ $conversation->id }}">
                                                <a href="javascript:void(0);" onclick="selectConversation('{{ $conversation->id }}', this)">
                                                    <div class="d-flex align-items-top">
                                                        <div class="me-1 lh-1">
                                                            <span class="avatar avatar-md online me-2 avatar-rounded">
                                                                <img src="{{ $otherUser->profile_image ? asset($otherUser->profile_image) : asset('build/assets/images/faces/9.jpg') }}" alt="img">
                                                            </span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <p class="mb-0 fw-medium">
                                                                {{ $otherUser->name }} 
                                                                <span class="float-end text-muted fw-normal fs-11">
                                                                    {{ $lastMsg ? $lastMsg->created_at->format('h:i A') : '' }}
                                                                </span>
                                                            </p>
                                                            <p class="fs-13 mb-0">
                                                                <span class="chat-msg text-truncate">
                                                                    {{ $lastMsg ? Str::limit($lastMsg->message, 30) : 'Start a conversation' }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-center p-3">No conversations yet.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="tab-pane border-0 chat-groups-tab" id="groups-tab-pane"
                                    role="tabpanel" aria-labelledby="groups-tab" tabindex="0">
                                    <ul class="list-unstyled mb-0 mt-2 chat-users-tab" id="chat-unread-msg-scroll">
                                        <li class="pb-0">
                                            <p class="text-muted fs-11 fw-medium mb-2 op-7">Unread Chats</p>
                                        </li>
                                        @php
                                            $unreadConversations = $conversations->filter(function($c) { return $c->unread_count > 0; });
                                        @endphp
                                        @forelse($unreadConversations as $conversation)
                                            @php
                                                $otherUser = (Auth::id() == $conversation->trainer_id) ? $conversation->client : $conversation->trainer;
                                                $lastMsg = $conversation->lastMessage;
                                            @endphp
                                            <li class="checkforactive" data-id="{{ $conversation->id }}">
                                                <a href="javascript:void(0);" onclick="selectConversation('{{ $conversation->id }}', this)">
                                                    <div class="d-flex align-items-top">
                                                        <div class="me-1 lh-1">
                                                            <span class="avatar avatar-md online me-2 avatar-rounded">
                                                                <img src="{{ $otherUser->profile_image ? asset($otherUser->profile_image) : asset('build/assets/images/faces/9.jpg') }}" alt="img">
                                                            </span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <p class="mb-0 fw-medium">
                                                                {{ $otherUser->name }} 
                                                                <span class="float-end text-muted fw-normal fs-11">
                                                                    {{ $lastMsg ? $lastMsg->created_at->format('h:i A') : '' }}
                                                                </span>
                                                            </p>
                                                            <p class="fs-13 mb-0">
                                                                <span class="chat-msg text-truncate">
                                                                    {{ $lastMsg ? Str::limit($lastMsg->message, 30) : 'Start a conversation' }}
                                                                </span>
                                                                <span class="chat-read-icon float-end align-middle badge bg-danger rounded-circle text-white" style="width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px;">{{ $conversation->unread_count }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @empty
                                            <li class="text-center p-3">No unread conversations.</li>
                                        @endforelse
                                    </ul>

                                </div>
                                <div class="tab-pane border-0 chat-contacts-tab" id="contacts-tab-pane" role="tabpanel"
                                    aria-labelledby="contacts-tab" tabindex="0">
                                    <ul class="list-unstyled mb-0 chat-contacts-tab" id="chat-contacts-scroll">
                                        <li class="pb-0">
                                            <p class="text-muted fs-11 fw-medium mb-2 op-7">Contacts</p>
                                        </li>
                                        @forelse($potentialChatPartners as $partner)
                                            <li class="checkforactive">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="lh-1">
                                                        <span class="avatar avatar-rounded avatar-sm">
                                                            <img src="{{ $partner->profile_image ? asset($partner->profile_image) : asset('build/assets/images/faces/9.jpg') }}" alt="">
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill">
                                                        <span class="d-block fw-semibold">
                                                            {{ $partner->name }}
                                                        </span>
                                                    </div>
                                                    <div class="dropdown">
                                                        <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="dropdown" class="btn btn-icon btn-sm btn-outline-light"> 
                                                            <i class="ri-more-2-fill"></i>
                                                        </a> 
                                                        <ul class="dropdown-menu" role="menu"> 
                                                            <li>
                                                                <form action="{{ route('chat.create') }}" method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="recipient_id" value="{{ $partner->id }}">
                                                                    <button type="submit" class="dropdown-item"><i class="ri-message-2-line me-2"></i>Chat</button>
                                                                </form>
                                                            </li> 
                                                        </ul> 
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="text-center p-3">No contacts found.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="main-chat-area border">
                            <div class="d-flex align-items-center border-bottom main-chat-head flex-wrap gap-1 d-none">
                                <div class="me-2 lh-1">
                                    <span class="avatar avatar-md online avatar-rounded chatstatusperson">
                                        <img class="chatimageperson" src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="mb-0 fw-medium fs-14 lh-1">
                                        <a href="javascript:void(0);" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" class="chatnameperson responsive-userinfo-open">Alice Smith</a>
                                    </p>
                                    <p class="text-muted mb-0 chatpersonstatus">online</p>
                                    <p class="text-success fs-11 fw-medium mb-0 d-none" id="typing-indicator">typing...</p>
                                </div>
                                <div class="d-flex align-items-center flex-wrap rightIcons">
                                    
                                    <button aria-label="button" type="button" class="btn btn-icon btn-light ms-2 responsive-userinfo-open btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                                        <i class="ti ti-user-circle"></i>
                                    </button>
                                    <div class="dropdown ms-2">
                                        <button aria-label="button" class="btn btn-icon btn-light btn-wave waves-light btn-sm" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="javascript:void(0);"><i class="ri-user-3-line me-1"></i>Profile</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);"><i class="ri-format-clear me-1"></i>Clear Chat</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);"><i class="ri-user-unfollow-line me-1"></i>Delete User</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);"><i class="ri-error-warning-line me-1"></i>Report</a></li>
                                        </ul>
                                    </div>
                                    <button aria-label="button" type="button" class="btn btn-icon btn-light ms-2 responsive-chat-close btn-sm">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="chat-content" id="main-chat-content">
                                <div class="chat-content-background">
                                    <img src="{{asset('build/assets/images/media/backgrounds/12.png')}}" alt="">
                                </div>
                                <div id="no-chat-selected" class="d-flex align-items-center justify-content-center h-100 flex-column">
                                    <div class="avatar avatar-xxl avatar-rounded bg-light text-muted mb-3">
                                        <i class="ri-chat-smile-2-line fs-1"></i>
                                    </div>
                                    <h4>Select a conversation</h4>
                                    <p class="text-muted">Choose a contact from the left sidebar to start chatting.</p>
                                </div>
                                <ul class="list-unstyled d-none" id="chat-messages-list">
                                    <!-- Dynamic messages will be loaded here -->
                                </ul>
                            </div>
                            <div class="chat-footer d-none">
                                <div id="chat-attachment-preview" class="d-none px-3 py-2 border-bottom bg-light d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm bg-primary-transparent text-primary me-2">
                                            <i class="ri-file-line" id="preview-icon"></i>
                                        </span>
                                        <span class="fs-12 fw-medium text-truncate" style="max-width: 200px;" id="preview-name">filename.jpg</span>
                                    </div>
                                    <button type="button" class="btn-close btn-sm" aria-label="Close" id="btn-remove-attachment"></button>
                                </div>
                                <input type="file" id="chat-file-input" style="display: none;">
                                <a aria-label="anchor" class="btn btn-icon me-2 btn-light" id="btn-attach" href="javascript:void(0)">
                                    <i class="ri-attachment-line"></i>
                                </a>
                                <input class="form-control form-control-lg chat-message-space border-0 shadow-none" placeholder="Type your message here..." type="text">
                                <a aria-label="anchor" class="btn btn-icon ms-2 btn-light emoji-picker" href="javascript:void(0)">
                                    <i class="ri-emotion-line"></i>
                                </a>
                                <a aria-label="anchor" class="btn btn-primary ms-2 btn-icon btn-send" href="javascript:void(0)">
                                    <i class="ri-send-plane-2-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Start::chat user details offcanvas -->
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight">
                        <div class="offcanvas-body">
                            <button type="button" class="btn-close btn btn-sm btn-icon btn-outline-light border" data-bs-dismiss="offcanvas"
                                aria-label="Close"></button>
                            <div class="chat-user-details" id="chat-user-details">
                                <div class="text-center mb-5">
                                    <span class="avatar avatar-rounded online avatar-xxl me-2 mb-3 chatstatusperson">
                                        <img class="chatimageperson" src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                    </span>
                                    <p class="mb-1 fs-15 fw-medium text-dark lh-1 chatnameperson">Alice Smith</p>
                                    <p class="fs-13 text-muted"><span class="chatnameperson">alicesmith</span>@gmail.com</p>
                                    <p class="text-center mb-0">
                                        <button type="button" aria-label="button" class="btn btn-icon rounded-pill btn-primary-light btn-wave"><i
                                                class="ri-phone-line"></i></button>
                                        <button type="button" aria-label="button" class="btn btn-icon rounded-pill btn-success-light ms-2 btn-wave"><i
                                                class="ri-chat-1-line"></i></button>
                                        <button type="button" aria-label="button" class="btn btn-icon rounded-pill btn-warning-light ms-2 btn-wave"><i
                                                class="ri-video-add-line"></i></button>
                                    </p>
                                </div>
                                <div class="mb-5">
                                    <div class="fw-medium mb-4">Shared Files
                                        <span class="float-end fs-11"><a href="javascript:void(0);" class="fs-13 text-muted"> View All<i class="ti ti-arrow-narrow-right ms-1"></i> </a></span>
                                    </div>
                                    <ul class="shared-files list-unstyled">
                                        <li class="text-center p-3">No shared files.</li>
                                    </ul>
                                </div>
                                <div class="mb-0">
                                    <div class="fw-medium mb-4">Photos & Media
                                        <span class="float-end fs-11"><a href="javascript:void(0);" class="fs-13 text-muted"> View All<i class="ti ti-arrow-narrow-right ms-1"></i> </a></span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12 text-center p-3">No media found.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End::chat user details offcanvas -->

    <!-- New Chat Modal -->
    <div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="newChatModalLabel">Start New Chat</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <ul class="list-group list-group-flush">
                        @if(isset($clients) && $clients->count() > 0)
                            <li class="list-group-item bg-light fw-bold text-uppercase fs-12 text-muted px-3 py-2">Subscribed Clients</li>
                            @foreach($clients as $client)
                                @if($client)
                                <li class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3" onclick="startNewChat('{{ $client->id }}')" style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm avatar-rounded me-2">
                                            <img src="{{ $client->profile_image ? asset($client->profile_image) : asset('build/assets/images/faces/9.jpg') }}" alt="img">
                                        </span>
                                        <div>
                                            <p class="mb-0 fw-semibold">{{ $client->name }}</p>
                                            <p class="fs-12 text-muted mb-0">{{ $client->email }}</p>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-icon btn-primary-light rounded-circle">
                                        <i class="ri-message-2-line"></i>
                                    </button>
                                </li>
                                @endif
                            @endforeach
                        @endif

                        @if(isset($trainers) && $trainers->count() > 0)
                            <li class="list-group-item bg-light fw-bold text-uppercase fs-12 text-muted px-3 py-2">Subscribed Trainers</li>
                            @foreach($trainers as $trainer)
                                @if($trainer)
                                <li class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3" onclick="startNewChat('{{ $trainer->id }}')" style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm avatar-rounded me-2">
                                            <img src="{{ $trainer->profile_image ? asset($trainer->profile_image) : asset('build/assets/images/faces/9.jpg') }}" alt="img">
                                        </span>
                                        <div>
                                            <p class="mb-0 fw-semibold">{{ $trainer->name }}</p>
                                            <p class="fs-12 text-muted mb-0">{{ $trainer->email }}</p>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-icon btn-primary-light rounded-circle">
                                        <i class="ri-message-2-line"></i>
                                    </button>
                                </li>
                                @endif
                            @endforeach
                        @endif

                        @if((!isset($clients) || $clients->isEmpty()) && (!isset($trainers) || $trainers->isEmpty()))
                            <div class="p-5 text-center">
                                <i class="ri-user-unfollow-line fs-1 mb-2 text-muted"></i>
                                <p class="text-muted mb-0">No active subscriptions found.</p>
                            </div>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
        <script>
            // Pass auth user data to JS
            window.authUserId = "{{ auth()->id() }}";
            window.authUserImage = "{{ auth()->user()->profile_image ? asset(auth()->user()->profile_image) : asset('build/assets/images/faces/9.jpg') }}";
        </script>
        
        <!-- Emojji Picker JS -->
        <script src="{{asset('build/assets/libs/fg-emoji-picker/fgEmojiPicker.js')}}"></script>
        
        <!-- Gallery JS -->
        <script src="{{asset('build/assets/libs/glightbox/js/glightbox.min.js')}}"></script>

        <!-- Chat JS -->
        @vite(['resources/js/chat-integration.js'])

@endsection