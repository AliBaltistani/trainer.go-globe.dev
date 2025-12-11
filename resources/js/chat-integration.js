import axios from 'axios';
import './bootstrap';

let currentConversationId = null;
const authUserId = document.querySelector('meta[name="user-id"]')?.content || window.authUserId;
const appendedMessageIds = new Set(); // Track appended messages to prevent duplicates

if (!authUserId) {
    console.error('User ID meta tag not found');
} else {
    // Global listener for real-time sidebar updates
    const initEchoListener = () => {
        if (window.Echo) {
            window.Echo.private(`user.${authUserId}`)
                .listen('.App\\Events\\MessageSent', (e) => {
                    handleIncomingMessage(e.message);
                });
        } else {
            // Echo might not be initialized yet, retry shortly
            setTimeout(initEchoListener, 500);
        }
    };

    // Start trying to listen once DOM is ready (or immediately if already ready)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEchoListener);
    } else {
        initEchoListener();
    }
}

function handleIncomingMessage(msg) {
    console.log('📨 [GLOBAL CHANNEL] Incoming message:', {
        messageId: msg.id,
        conversationId: msg.conversation_id,
        currentConversationId: currentConversationId,
        isCurrentConversation: currentConversationId == msg.conversation_id
    });

    const conversationId = msg.conversation_id;

    // 1. Update Recent List (Always update preview and move to top)
    const recentList = document.getElementById('chat-msg-scroll');
    let recentItem = null;

    if (recentList) {
        recentItem = recentList.querySelector(`li[data-id="${conversationId}"]`);

        // If not in recent list, create it (New Conversation)
        // Ensure msg.sender exists before trying to access properties
        if (!recentItem && msg.sender) {
            console.log('Creating new conversation item for:', conversationId);
            recentItem = document.createElement('li');
            recentItem.className = 'checkforactive';
            recentItem.setAttribute('data-id', conversationId);

            const senderName = msg.sender.name || 'Unknown';
            const senderImg = msg.sender.profile_image ? `/${msg.sender.profile_image}` : '/build/assets/images/faces/9.jpg';
            const date = new Date(msg.created_at);
            const time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const text = msg.message_type === 'text' ? msg.message : 'Attachment';
            const previewText = text.length > 30 ? text.substring(0, 30) + '...' : text;

            recentItem.innerHTML = `
                <a href="javascript:void(0);" onclick="selectConversation('${conversationId}', this)">
                    <div class="d-flex align-items-top">
                        <div class="me-1 lh-1">
                            <span class="avatar avatar-md online me-2 avatar-rounded">
                                <img src="${senderImg}" alt="img">
                            </span>
                        </div>
                        <div class="flex-fill">
                            <p class="mb-0 fw-medium">
                                ${senderName}
                                <span class="float-end text-muted fw-normal fs-11">
                                    ${time}
                                </span>
                            </p>
                            <p class="fs-13 mb-0">
                                <span class="chat-msg text-truncate">
                                    ${previewText}
                                </span>
                            </p>
                        </div>
                    </div>
                </a>
            `;

            recentList.prepend(recentItem);

            // Remove "No conversations" placeholder
            const noConv = recentList.querySelector('li.text-center');
            if (noConv) noConv.remove();
        } else if (recentItem) {
            updateListItemContent(recentItem, msg);
            recentList.prepend(recentItem);
        }
    }

    // 2. If this is the current conversation, append the message immediately
    if (currentConversationId == conversationId) {
        console.log('✅ [GLOBAL] Message is for current conversation, appending...');
        appendMessage(msg);
        scrollToBottom();

        // Mark as read immediately since we are viewing it
        axios.post('/chat/messages/read', { conversation_id: conversationId })
            .catch(err => console.error('Failed to mark as read:', err));

        return; // Don't show unread badge for current conversation
    }

    // 3. Handle Unread Status (If not current conversation)
    console.log('Message is for different conversation, updating badges...');

    // 3a. Update Badge on Recent List Item (if exists)
    if (recentItem) {
        let badge = recentItem.querySelector('.chat-read-icon');
        if (!badge) {
            const p = recentItem.querySelector('.fs-13');
            if (p) {
                badge = document.createElement('span');
                badge.className = 'chat-read-icon float-end align-middle badge bg-danger rounded-circle text-white';
                badge.style.cssText = 'width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px;';
                badge.textContent = '0';
                p.appendChild(badge);
            }
        }
        if (badge) {
            let count = parseInt(badge.textContent) || 0;
            badge.textContent = count + 1;
            console.log('Updated badge count to:', count + 1);
        }
    }

    // 3b. Update Unread List
    const unreadList = document.getElementById('chat-unread-msg-scroll');
    if (unreadList) {
        let unreadItem = unreadList.querySelector(`li[data-id="${conversationId}"]`);

        if (unreadItem) {
            // Already in unread list, just update
            updateListItemContent(unreadItem, msg);

            // Increment badge
            const badge = unreadItem.querySelector('.chat-read-icon');
            if (badge) {
                let count = parseInt(badge.textContent) || 0;
                badge.textContent = count + 1;
            }
            unreadList.prepend(unreadItem);

            // Update Tab Badges (1 new message, 0 new conversations)
            updateTabBadges(1, 0);

        } else if (recentItem) {
            // Not in unread list, clone from recent (which now has the badge)
            unreadItem = recentItem.cloneNode(true);

            unreadList.prepend(unreadItem);

            // Remove "No unread conversations" placeholder
            const noMsg = unreadList.querySelector('li.text-center');
            if (noMsg) noMsg.remove();

            // Update Tab Badges (1 new message, 1 new conversation)
            updateTabBadges(1, 1);
        }
    }
}

function updateListItemContent(li, msg) {
    // Update Message Preview
    const msgPreview = li.querySelector('.chat-msg');
    if (msgPreview) {
        // Truncate to ~30 chars
        const text = msg.message_type === 'text' ? msg.message : 'Attachment';
        msgPreview.textContent = text.length > 30 ? text.substring(0, 30) + '...' : text;
    }

    // Update Time
    const timeSpan = li.querySelector('.float-end.text-muted');
    if (timeSpan) {
        const date = new Date(msg.created_at);
        timeSpan.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
}

function updateTabBadges(messageIncrement = 0, conversationIncrement = 0) {
    // 1. Update "Recents" tab badge (Total Unread Messages)
    const recentsTab = document.getElementById('users-tab');
    if (recentsTab && messageIncrement !== 0) {
        let badge = recentsTab.querySelector('.badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge bg-secondary ms-1 rounded-pill';
            badge.textContent = '0';
            recentsTab.appendChild(badge);
        }
        let newCount = (parseInt(badge.textContent) || 0) + messageIncrement;
        badge.textContent = Math.max(0, newCount);
        if (newCount <= 0) badge.remove();
    }

    // 2. Update "Unread" tab badge (Unread Conversations Count)
    const unreadTab = document.getElementById('groups-tab');
    if (unreadTab && conversationIncrement !== 0) {
        let badge = unreadTab.querySelector('.badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge bg-secondary ms-1 rounded-pill';
            badge.textContent = '0';
            unreadTab.appendChild(badge);
        }
        let newCount = (parseInt(badge.textContent) || 0) + conversationIncrement;
        badge.textContent = Math.max(0, newCount);
        if (newCount <= 0) badge.remove();
    }
}

// Initialize Emoji Picker
let emojiPicker;
document.addEventListener('DOMContentLoaded', () => {
    const emojiBtn = document.querySelector('.emoji-picker');
    if (emojiBtn && typeof FgEmojiPicker !== 'undefined') {
        emojiPicker = new FgEmojiPicker({
            trigger: ['.emoji-picker'],
            dir: '/build/assets/libs/fg-emoji-picker/',
            removeOnSelection: false,
            closeButton: true,
            position: ['top', 'right', 'bottom', 'left'],
            preFetch: true,
            insertInto: document.querySelector('.chat-message-space'),
            emit(obj, triggerElement) {
                const input = document.querySelector('.chat-message-space');
                if (input) {
                    input.value += obj.emoji;
                }
            }
        });
    }

    // Initialize listeners
    const sendBtn = document.querySelector('.btn-send');
    const input = document.querySelector('.chat-message-space');
    const btnAttach = document.getElementById('btn-attach');
    const fileInput = document.getElementById('chat-file-input');
    const btnRemoveAttachment = document.getElementById('btn-remove-attachment');

    // Responsive Chat Close Button
    const responsiveCloseBtn = document.querySelector('.responsive-chat-close');
    if (responsiveCloseBtn) {
        responsiveCloseBtn.addEventListener('click', () => {
            const chartWrapper = document.querySelector(".main-chart-wrapper");
            if (chartWrapper) {
                chartWrapper.classList.remove("responsive-chat-open");
            }
        });
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }

    if (input) {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }

    if (btnAttach && fileInput) {
        btnAttach.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                window.selectedFile = e.target.files[0];
                btnAttach.classList.add('text-primary'); // Indicate file selected

                // Show Preview
                const previewDiv = document.getElementById('chat-attachment-preview');
                const previewName = document.getElementById('preview-name');
                const previewIcon = document.getElementById('preview-icon');

                if (previewDiv && previewName && previewIcon) {
                    previewName.textContent = window.selectedFile.name;
                    previewIcon.className = getFileIcon(window.selectedFile.name);
                    previewDiv.classList.remove('d-none');
                }
            } else {
                clearAttachment();
            }
        });
    }

    if (btnRemoveAttachment) {
        btnRemoveAttachment.addEventListener('click', clearAttachment);
    }

    // Initialize GLightbox for any existing images (if any)
    if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '.glightbox' });
    }
});

function clearAttachment() {
    window.selectedFile = null;
    const fileInput = document.getElementById('chat-file-input');
    if (fileInput) fileInput.value = '';

    const btnAttach = document.getElementById('btn-attach');
    if (btnAttach) btnAttach.classList.remove('text-primary');

    const previewDiv = document.getElementById('chat-attachment-preview');
    if (previewDiv) previewDiv.classList.add('d-none');
}

window.selectConversation = function (conversationId, element) {
    if (currentConversationId) {
        window.Echo.leave(`conversation.${currentConversationId}`);
    }
    currentConversationId = conversationId;

    // Clear message tracking for new conversation
    appendedMessageIds.clear();
    console.log('🔄 Switched to conversation:', conversationId);

    // UI updates: Mark active in ALL lists
    document.querySelectorAll('.checkforactive').forEach(el => el.classList.remove('active'));
    document.querySelectorAll(`li[data-id="${conversationId}"]`).forEach(el => el.classList.add('active'));

    // If element is missing, try to find one to update header info
    if (!element) {
        element = document.querySelector(`li[data-id="${conversationId}"]`);
    }

    if (element) {
        // Update header info
        const nameElement = element.querySelector('.fw-medium');
        const name = nameElement ? nameElement.childNodes[0].textContent.trim() : 'Unknown';
        const imgElement = element.querySelector('img');
        const img = imgElement ? imgElement.src : '';
        const statusElement = element.querySelector('.avatar');
        const status = statusElement && statusElement.classList.contains('online') ? 'online' : 'offline';

        document.querySelectorAll(".chatnameperson").forEach(el => el.innerText = name);
        document.querySelectorAll(".chatimageperson").forEach(el => el.src = img);
        document.querySelectorAll(".chatstatusperson").forEach(el => {
            el.classList.remove("online", "offline");
            el.classList.add(status);
        });
        const statusText = document.querySelector(".chatpersonstatus");
        if (statusText) statusText.innerText = status;

        const chartWrapper = document.querySelector(".main-chart-wrapper");
        if (chartWrapper) chartWrapper.classList.add("responsive-chat-open");
    }

    // Show chat area, hide placeholder
    const noChatPlaceholder = document.getElementById('no-chat-selected');
    const chatList = document.getElementById('chat-messages-list');
    const chatHead = document.querySelector('.main-chat-head');
    const chatFooter = document.querySelector('.chat-footer');

    if (noChatPlaceholder) {
        noChatPlaceholder.classList.add('d-none');
        noChatPlaceholder.classList.remove('d-flex');
    }
    if (chatList) {
        chatList.classList.remove('d-none');
    }
    if (chatHead) chatHead.classList.remove('d-none');
    if (chatFooter) chatFooter.classList.remove('d-none');

    // Fetch messages
    axios.get(`/chat/messages/${conversationId}`)
        .then(response => {
            // Handle paginated response (data.data is the array for Laravel pagination JSON)
            // We get them desc (newest first), so we reverse to show oldest first
            const messages = response.data.data.reverse();
            renderMessages(messages);
            scrollToBottom();

            // Optimistic UI Update: Clear badges immediately
            const listItems = document.querySelectorAll(`li[data-id="${conversationId}"]`);
            let optimisticUnreadCount = 0;

            listItems.forEach(li => {
                const badge = li.querySelector('.chat-read-icon');
                if (badge) {
                    const count = parseInt(badge.textContent) || 0;
                    if (count > optimisticUnreadCount) optimisticUnreadCount = count;
                    badge.remove();
                }
            });

            // Remove from "Unread" list specifically
            const unreadList = document.getElementById('chat-unread-msg-scroll');
            if (unreadList) {
                const unreadItem = unreadList.querySelector(`li[data-id="${conversationId}"]`);
                if (unreadItem) {
                    unreadItem.remove();

                    // If list is empty (only title remains), show placeholder
                    if (unreadList.querySelectorAll('li').length <= 1) {
                        const noMsg = document.createElement('li');
                        noMsg.className = 'text-center p-3';
                        noMsg.textContent = 'No unread conversations.';
                        unreadList.appendChild(noMsg);
                    }
                }
            }

            // Update Tab Badges (decrement)
            if (optimisticUnreadCount > 0) {
                updateTabBadges(-optimisticUnreadCount, -1);
            }

            // Mark as read in backend
            console.log('Marking messages as read for conversation:', conversationId);
            axios.post('/chat/messages/read', { conversation_id: conversationId })
                .then((readResponse) => {
                    console.log('Messages marked as read successfully');
                    // We already updated UI optimistically, so we don't need to do it here
                    // unless we want to reconcile counts, but for now optimistic is smoother.
                })
                .catch(err => {
                    console.error('Failed to mark messages as read', err);
                    // Revert UI changes if needed? Complex. 
                    // For now, assume success or user will refresh.
                });

            // Listen for new messages and read status updates
            if (window.Echo) {
                let typingTimer;
                const channel = window.Echo.private(`conversation.${conversationId}`);

                console.log('📡 Listening on conversation channel:', conversationId);

                channel
                    .listen('.App\\Events\\MessageSent', (e) => {
                        console.log('📩 [CONVERSATION CHANNEL] Received MessageSent event:', {
                            messageId: e.message.id,
                            senderId: e.message.sender_id,
                            receiverId: e.message.receiver_id,
                            currentUserId: authUserId,
                            conversationId: e.message.conversation_id
                        });

                        if (e.message.sender_id != authUserId) {
                            console.log('✅ Message from other user, appending to conversation...');
                            appendMessage(e.message);
                            scrollToBottom();

                            // Mark as read immediately since we are viewing it
                            axios.post('/chat/messages/read', { conversation_id: conversationId })
                                .catch(err => console.error('Failed to mark as read:', err));
                        } else {
                            console.log('⏭️ Own message, skipping (already displayed)');
                        }
                    })
                    .listen('.App\\Events\\MessageRead', (e) => {
                        if (e.userId != authUserId) {
                            // Update read status for all my messages (chat-item-end)
                            document.querySelectorAll('.chat-item-end .chat-read-mark').forEach(mark => {
                                mark.classList.remove('ri-check-line');
                                mark.classList.add('ri-check-double-line');
                                mark.classList.add('text-success');
                            });
                        }
                    })
                    .listenForWhisper('typing', (e) => {
                        if (e.userId != authUserId) {
                            console.log('⌨️ User is typing...');
                            const typingIndicator = document.getElementById('typing-indicator');
                            if (typingIndicator) {
                                typingIndicator.classList.remove('d-none');

                                clearTimeout(typingTimer);
                                typingTimer = setTimeout(() => {
                                    typingIndicator.classList.add('d-none');
                                }, 3000);
                            }
                        }
                    });

                // Setup typing trigger
                const input = document.querySelector('.chat-message-space');
                if (input) {
                    input.oninput = () => {
                        channel.whisper('typing', {
                            userId: authUserId
                        });
                    };
                }
            }
        })
        .catch(error => console.error(error));
};

function renderMessages(messages) {
    const chatList = document.getElementById('chat-messages-list');
    if (!chatList) return;

    chatList.innerHTML = '';

    messages.forEach(msg => {
        appendMessage(msg);
    });

    // Re-init GLightbox
    if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '.glightbox' });
    }
}

function appendMessage(msg) {
    const chatList = document.getElementById('chat-messages-list');
    console.log("chatList:", chatList);
    if (!chatList) return;

    // Prevent duplicate messages
    if (msg.id && appendedMessageIds.has(msg.id)) {
        console.log('⚠️ Message already appended, skipping:', msg.id);
        return;
    }

    const isMe = msg.sender_id == authUserId;
    const alignClass = isMe ? 'chat-item-end' : 'chat-item-start';

    // Use stored partner avatar or fallback
    let avatarSrc = '';
    if (isMe) {
        avatarSrc = window.authUserImage || '/build/assets/images/faces/9.jpg';
    } else {
        // Try to get partner image from header or default
        const headerImg = document.querySelector('.chatimageperson');
        avatarSrc = headerImg ? headerImg.src : '/build/assets/images/faces/4.jpg';
    }

    // Format time
    const date = new Date(msg.created_at);
    const time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    let contentHtml = '';
    if (msg.message_type === 'text') {
        contentHtml = `<p class="mb-0">${msg.message}</p>`;
    } else if (msg.message_type === 'image') {
        contentHtml = `
            <div class="chat-media">
                <a href="/${msg.media_url}" class="glightbox">
                    <img src="/${msg.media_url}" class="img-fluid rounded" style="max-width: 200px;" alt="image">
                </a>
            </div>`;
    } else {
        const fileName = msg.media_url.split('/').pop();
        const iconClass = getFileIcon(fileName);
        // Calculate size (mock) or hide
        contentHtml = `
            <div class="chat-attachment-card">
                <div class="chat-attachment-icon text-primary">
                    <i class="${iconClass}"></i>
                </div>
                <div class="chat-attachment-info">
                    <div class="chat-attachment-name ${isMe ? 'text-white' : 'text-dark'}">${fileName}</div>
                    <div class="chat-attachment-size ${isMe ? 'text-white-50' : 'text-muted'}">Attachment</div>
                </div>
                <a href="/${msg.media_url}" target="_blank" class="btn btn-sm btn-icon btn-light ms-2 rounded-circle">
                    <i class="ri-download-2-line"></i>
                </a>
            </div>`;
    }

    // Status Icon
    let statusIcon = '';
    if (isMe) {
        const isRead = msg.is_read;
        const iconClass = isRead ? 'ri-check-double-line text-white' : 'ri-check-line text-white-50';
        statusIcon = `<i class="${iconClass} ms-1 fs-12 chat-read-mark"></i>`;
    }

    const html = `
        <li class="chat-item ${alignClass}">
            <div class="chat-item-box">
                ${!isMe ? `<img src="${avatarSrc}" alt="img" class="chat-avatar-img">` : ''}
                <div class="chat-item-content">
                    <div class="chat-item-text">
                        ${contentHtml}
                    </div>
                    <div class="chat-item-meta ${isMe ? 'text-end' : ''}">
                        <span class="fs-11 ${isMe ? 'text-muted' : 'text-muted'}">${time}</span>
                        ${statusIcon}
                    </div>
                </div>
                ${isMe ? `<img src="${avatarSrc}" alt="img" class="chat-avatar-img">` : ''}
            </div>
        </li>
    `;

    chatList.insertAdjacentHTML('beforeend', html);

    // Track this message as appended
    if (msg.id) {
        appendedMessageIds.add(msg.id);
    }
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    switch (ext) {
        case 'pdf': return 'ri-file-pdf-line';
        case 'doc': case 'docx': return 'ri-file-word-line';
        case 'xls': case 'xlsx': return 'ri-file-excel-line';
        case 'ppt': case 'pptx': return 'ri-file-ppt-line';
        case 'zip': case 'rar': return 'ri-file-zip-line';
        case 'jpg': case 'jpeg': case 'png': case 'gif': return 'ri-image-line';
        case 'mp4': case 'avi': case 'mov': return 'ri-video-line';
        case 'mp3': case 'wav': return 'ri-music-line';
        default: return 'ri-file-line';
    }
}

window.sendMessage = function () {
    const input = document.querySelector('.chat-message-space');
    const sendBtn = document.querySelector('.btn-send');
    const message = input.value;

    if (!message.trim() && !window.selectedFile) return;
    if (!currentConversationId) return;

    // Disable button
    if (sendBtn) {
        sendBtn.disabled = true;
        const originalContent = sendBtn.innerHTML;
        sendBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';
        sendBtn.dataset.originalContent = originalContent;
    }

    const formData = new FormData();
    formData.append('conversation_id', currentConversationId);
    if (message.trim()) formData.append('message', message);
    if (window.selectedFile) formData.append('file', window.selectedFile);

    axios.post('/chat/send', formData)
        .then(response => {
            input.value = '';

            clearAttachment();

            appendMessage(response.data.message);
            scrollToBottom();

            // Re-init GLightbox for new image if sent
            if (typeof GLightbox !== 'undefined' && response.data.message.message_type === 'image') {
                GLightbox({ selector: '.glightbox' });
            }
        })
        .catch(error => console.error(error))
        .finally(() => {
            // Re-enable button
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = sendBtn.dataset.originalContent || '<i class="ri-send-plane-2-line"></i>';
            }
        });
};

function scrollToBottom() {
    // Priority: 1. Main Chat Content ID, 2. SimpleBar Wrapper
    const chatContent = document.getElementById('main-chat-content');
    const simpleBar = document.querySelector('.chat-content .simplebar-content-wrapper');

    // Helper to scroll
    const performScroll = (element) => {
        if (element) {
            // Scroll immediately
            element.scrollTop = element.scrollHeight;

            // And again after a short delay to account for images/reflows
            setTimeout(() => {
                element.scrollTop = element.scrollHeight;
            }, 100);

            // And one more time for good measure if images are slow
            setTimeout(() => {
                element.scrollTop = element.scrollHeight;
            }, 300);
        }
    };

    if (chatContent && chatContent.offsetParent !== null) { // Check if visible
        performScroll(chatContent);
    } else if (simpleBar) {
        performScroll(simpleBar);
    }
}

window.startNewChat = function (partnerId) {
    axios.post('/chat/create', { partner_id: partnerId })
        .then(response => {
            const conversation = response.data.conversation;

            // Reload to update list and select the conversation
            window.location.reload();
        })
        .catch(error => {
            console.error('Error creating chat:', error);
            if (error.response && error.response.status === 403) {
                alert('You are not authorized to chat with this user (subscription inactive).');
            } else {
                alert('Failed to start chat. Please try again.');
            }
        });
};
