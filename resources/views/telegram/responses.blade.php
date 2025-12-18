@extends('layouts.coreui')

@section('title', 'Phản hồi từ Telegram')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('telegram.index') }}">Telegram</a></li>
        <li class="breadcrumb-item active">Phản hồi</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-3" id="responseTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="callbacks-tab" data-bs-toggle="tab" data-bs-target="#callbacks" type="button" role="tab" aria-controls="callbacks" aria-selected="true">
                        <i class="bi bi-chat-square-text me-1"></i> Phản hồi Button
                        <span class="badge bg-primary ms-1" id="callbacksCount">{{ $callbacksGrouped->flatten()->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab" aria-controls="messages" aria-selected="false">
                        <i class="bi bi-chat-dots me-1"></i> Tin nhắn Text
                        <span class="badge bg-success ms-1" id="messagesCount">{{ $messagesGrouped->flatten()->count() }}</span>
                    </button>
                </li>
            </ul>

            <!-- New responses notification -->
            <div id="newCallbacksAlert" class="alert alert-info m-3 d-none" role="alert">
                <i class="bi bi-bell me-2"></i>
                <span id="newCallbacksCount">0</span> phản hồi mới!
                <button type="button" class="btn btn-sm btn-info ms-2" onclick="scrollToTop()">
                    Xem ngay
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="responseTabsContent">
                <!-- Tab: Callbacks Only -->
                <div class="tab-pane fade show active" id="callbacks" role="tabpanel" aria-labelledby="callbacks-tab">
                    <div id="callbacksOnlyContainer">
                        @forelse($callbacksGrouped as $messageId => $callbacks)
                            @php
                                $firstCallback = $callbacks->first();
                                $messageText = $firstCallback->message_text ?? 'Không có nội dung';
                            @endphp
                            <div class="message-group mb-4" data-message-id="{{ $messageId }}">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-gradient-primary text-white border-0">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-envelope-fill me-2 fs-5"></i>
                                                <div>
                                                    <div class="fw-bold fs-6">Message ID: {{ $messageId ?? 'N/A' }}</div>
                                                    <small class="text-white-50">
                                                        @if ($firstCallback)
                                                            {{ $firstCallback->created_at->format('d/m/Y') }} •
                                                            {{ $firstCallback->created_at->diffForHumans() }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                            <span class="badge bg-light text-dark message-count">{{ $callbacks->count() }} phản hồi</span>
                                        </div>
                                        @if ($messageText)
                                            <div class="message-content mt-3 p-3 bg-white bg-opacity-10 rounded">
                                                <div class="d-flex align-items-start">
                                                    <i class="bi bi-quote me-2 mt-1"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 text-white" style="line-height: 1.6;">{{ $messageText }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="responses-list callbacks-body" data-message-id="{{ $messageId }}">
                                            @foreach ($callbacks as $index => $callback)
                                                <div class="response-item p-3 {{ $index > 0 ? 'border-top' : '' }}" data-id="{{ $callback->id }}" data-type="callback">
                                                    <div class="d-flex align-items-start">
                                                        <div class="avatar me-3 flex-shrink-0">
                                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($callback->telegram_full_name) }}&background=0088cc&color=fff&size=48"
                                                                class="rounded-circle" style="width: 48px; height: 48px;"
                                                                alt="{{ $callback->telegram_full_name }}">
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <div class="fw-semibold text-dark">{{ $callback->telegram_full_name }}</div>
                                                                    <div class="small text-muted">
                                                                        @if ($callback->telegram_username)
                                                                            <span class="text-primary">{{ $callback->telegram_username }}</span>
                                                                        @endif
                                                                        @if ($callback->user)
                                                                            <span class="ms-2">
                                                                                <i class="bi bi-link-45deg"></i>
                                                                                {{ $callback->user->name }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <div class="small text-muted">{{ $callback->created_at->format('H:i:s') }}</div>
                                                                    <div class="small text-muted">{{ $callback->created_at->diffForHumans() }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="response-badge">
                                                                <span class="badge bg-primary fs-6 px-3 py-2">
                                                                    <i class="bi bi-chat-square-text me-1"></i>
                                                                    {{ $callback->callback_data }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">Chưa có phản hồi button nào</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Tab: Messages Only -->
                <div class="tab-pane fade" id="messages" role="tabpanel" aria-labelledby="messages-tab">
                    <div id="messagesOnlyContainer">
                        @forelse($messagesGrouped as $replyToMessageId => $messages)
                            @php
                                $firstMessage = $messages->first();
                            @endphp
                            <div class="message-group mb-4" data-message-id="reply-{{ $replyToMessageId }}">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-gradient-success text-white border-0">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-chat-dots-fill me-2 fs-5"></i>
                                                <div>
                                                    <div class="fw-bold fs-6">Tin nhắn phản hồi</div>
                                                    <small class="text-white-50">
                                                        @if ($firstMessage)
                                                            {{ $firstMessage->created_at->format('d/m/Y') }} •
                                                            {{ $firstMessage->created_at->diffForHumans() }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                            <span class="badge bg-light text-dark message-count">{{ $messages->count() }} tin nhắn</span>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="responses-list messages-body" data-message-id="reply-{{ $replyToMessageId }}">
                                            @foreach ($messages as $index => $message)
                                                <div class="response-item p-3 {{ $index > 0 ? 'border-top' : '' }}" data-id="{{ $message->id }}" data-type="message">
                                                    <div class="d-flex align-items-start">
                                                        <div class="avatar me-3 flex-shrink-0">
                                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($message->telegram_full_name) }}&background=28a745&color=fff&size=48"
                                                                class="rounded-circle" style="width: 48px; height: 48px;"
                                                                alt="{{ $message->telegram_full_name }}">
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <div class="fw-semibold text-dark">{{ $message->telegram_full_name }}</div>
                                                                    <div class="small text-muted">
                                                                        @if ($message->telegram_username)
                                                                            <span class="text-primary">{{ $message->telegram_username }}</span>
                                                                        @endif
                                                                        @if ($message->user)
                                                                            <span class="ms-2">
                                                                                <i class="bi bi-link-45deg"></i>
                                                                                {{ $message->user->name }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <div class="small text-muted">{{ $message->created_at->format('H:i:s') }}</div>
                                                                    <div class="small text-muted">{{ $message->created_at->diffForHumans() }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="message-text mt-2 p-2 bg-light rounded">
                                                                <p class="mb-0">{{ $message->text }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">Chưa có tin nhắn text nào</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(150deg, #667eea 0%, #764ba2 100%);
        }

        .message-group .card {
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .message-group .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .message-content {
            backdrop-filter: blur(10px);
            border-left: 3px solid rgba(255, 255, 255, 0.5);
        }

        .response-item {
            transition: background-color 0.2s;
        }

        .response-item:hover {
            background-color: #f8f9fa;
        }

        .response-item.border-top {
            border-top: 1px solid #e9ecef !important;
        }

        .callback-new {
            animation: highlightNew 2s ease-out;
            background-color: rgba(25, 135, 84, 0.1);
        }

        @keyframes highlightNew {
            0% {
                background-color: rgba(25, 135, 84, 0.2);
            }

            100% {
                background-color: transparent;
            }
        }

        #liveIndicator i {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .avatar img {
            border: 2px solid #e9ecef;
            transition: border-color 0.2s;
        }

        .response-item:hover .avatar img {
            border-color: #667eea;
        }

        .response-badge .badge {
            border-radius: 8px;
            font-weight: 500;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .message-text {
            word-wrap: break-word;
            white-space: pre-wrap;
        }

        /* Tab styles */
        .nav-tabs .nav-link {
            cursor: pointer;
            border: 1px solid transparent;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
        }

        .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #dee2e6;
        }

        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.show.active {
            display: block;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            let latestId = {{ $latestCallbackId ?? 0 }};
            $('#callbacksOnlyContainer .response-item[data-type="callback"]').each(function() {
                const id = parseInt($(this).data('id')) || 0;
                if (id > latestId) latestId = id;
            });

            let latestMessageId = {{ $latestMessageId ?? 0 }};
            $('#messagesOnlyContainer .response-item[data-type="message"]').each(function() {
                const id = parseInt($(this).data('id')) || 0;
                if (id > latestMessageId) latestMessageId = id;
            });

            let pollInterval = 3000;
            let pollTimer = null;
            let newCallbacksCount = 0;

            function startPolling() {
                setTimeout(function() {
                    fetchNewCallbacks();
                    fetchNewMessages();
                }, 1000);

                pollTimer = setInterval(function() {
                    fetchNewCallbacks();
                    fetchNewMessages();
                }, pollInterval);
                updateLastUpdateTime();
            }

            function stopPolling() {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
            }
            function fetchNewCallbacks() {
                $.ajax({
                    url: '{{ route('telegram.callbacks.new') }}',
                    method: 'GET',
                    data: {
                        since_id: latestId
                    },
                    success: function(response) {
                        console.log('fetchNewCallbacks response:', response);
                        if (response.success && response.data.length > 0) {
                            console.log('Found', response.data.length, 'new callbacks, latestId:', latestId);
                            $('#emptyRow').remove();

                            let hasNewCallbacks = false;
                            response.data.reverse().forEach(function(callback) {
                                if (callback.id > latestId) {
                                    prependCallback(callback);
                                    newCallbacksCount++;
                                    hasNewCallbacks = true;
                                    latestId = Math.max(latestId, callback.id);
                                }
                            });

                            if (response.latest_id > latestId) {
                                latestId = response.latest_id;
                            }

                            console.log('Final latestId:', latestId);

                            if (hasNewCallbacks) {
                                showNewCallbacksAlert();
                                if (currentTab === 'callbacks') {
                                    $('html, body').animate({ scrollTop: 0 }, 300);
                                }
                            }
                        }
                    },
                    error: function() {
                        console.error('Failed to fetch new callbacks');
                    }
                });
            }

            function fetchNewMessages() {
                $.ajax({
                    url: '{{ route('telegram.messages.new') }}',
                    method: 'GET',
                    data: {
                        since_id: latestMessageId
                    },
                    success: function(response) {
                        console.log('fetchNewMessages response:', response);
                        if (response.success && response.data.length > 0) {
                            console.log('Found', response.data.length, 'new messages');
                            $('#emptyRow').remove();

                            let hasNewMessages = false;
                            response.data.reverse().forEach(function(message) {
                                console.log('Processing message:', message.id, 'latestMessageId:', latestMessageId);
                                if (message.id > latestMessageId) {
                                    prependMessage(message);
                                    newCallbacksCount++;
                                    hasNewMessages = true;
                                    latestMessageId = Math.max(latestMessageId, message.id);
                                } else {
                                    console.log('Message ID not greater than latest:', message.id, '<=', latestMessageId);
                                }
                            });

                            if (response.latest_id > latestMessageId) {
                                latestMessageId = response.latest_id;
                            }

                            console.log('Final latestMessageId:', latestMessageId);

                            if (hasNewMessages) {
                                showNewCallbacksAlert();
                                if (currentTab === 'messages') {
                                    $('html, body').animate({ scrollTop: 0 }, 300);
                                }
                            }
                        } else {
                            console.log('No new messages or empty response');
                        }
                        updateLastUpdateTime();
                    },
                    error: function() {
                        console.error('Failed to fetch new messages');
                    }
                });
            }

            function prependCallback(callback) {
                if ($(`.response-item[data-id="${callback.id}"][data-type="callback"]`).length > 0) {
                    console.log('Callback already exists:', callback.id);
                    return;
                }

                console.log('Prepending callback:', callback.id, callback.callback_data);

                const linkedUser = callback.user_name ?
                    `<span class="ms-2"><i class="bi bi-link-45deg"></i> ${callback.user_name}</span>` :
                    '';

                const username = callback.display_name.startsWith('@') ?
                    `<span class="text-primary">@${callback.display_name.substring(1)}</span>` :
                    '';

                const messageId = callback.message_id || 'N/A';

                let $messageGroup = $(`#callbacksOnlyContainer .message-group[data-message-id="${messageId}"]`);

                if ($messageGroup.length === 0) {
                    createMessageGroup(messageId, callback);
                    $messageGroup = $(`#callbacksOnlyContainer .message-group[data-message-id="${messageId}"]`);
                }

                const $responsesList = $messageGroup.find(`.callbacks-body[data-message-id="${messageId}"]`);
                const isFirstItem = $responsesList.find('.response-item').length === 0;
                const borderClass = isFirstItem ? '' : 'border-top';

                const html = `
            <div class="response-item p-3 ${borderClass} callback-new" data-id="${callback.id}" data-type="callback">
                <div class="d-flex align-items-start">
                    <div class="avatar me-3 flex-shrink-0">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(callback.telegram_full_name)}&background=0088cc&color=fff&size=48"
                             class="rounded-circle"
                             style="width: 48px; height: 48px;"
                             alt="${callback.telegram_full_name}">
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold text-dark">${callback.telegram_full_name}</div>
                                <div class="small text-muted">
                                    ${username}
                                    ${linkedUser}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">${callback.created_at.split(' ')[1] || ''}</div>
                                <div class="small text-muted">${callback.time_ago}</div>
                            </div>
                        </div>
                        <div class="response-badge">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                <i class="bi bi-chat-square-text me-1"></i>
                                ${callback.callback_data}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;

                $responsesList.prepend(html);

                updateMessageGroupCount(messageId);
                $messageGroup.prependTo('#callbacksOnlyContainer');
                updateTabCounts();
            }

            function createMessageGroup(messageId, firstCallback) {
                const messageText = firstCallback.message_text || 'Không có nội dung';
                const createdDate = firstCallback.created_at || '';
                const timeAgo = firstCallback.time_ago || 'Vừa xong';

                const html = `
            <div class="message-group mb-4" data-message-id="${messageId}">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-primary text-white border-0">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope-fill me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold fs-6">Message ID: ${messageId}</div>
                                    <small class="text-white-50">${createdDate} • ${timeAgo}</small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark message-count">1 phản hồi</span>
                        </div>
                        ${messageText ? `
                                <div class="message-content mt-3 p-3 bg-white bg-opacity-10 rounded">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-quote me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 text-white" style="line-height: 1.6;">${messageText}</p>
                                        </div>
                                    </div>
                                </div>
                                ` : ''}
                    </div>
                    <div class="card-body p-0">
                        <div class="responses-list callbacks-body" data-message-id="${messageId}">
                        </div>
                    </div>
                </div>
            </div>
        `;

                $('#callbacksOnlyContainer').prepend(html);
                $('#emptyRow').remove();
            }

            function prependMessage(message) {
                if ($(`.response-item[data-id="${message.id}"][data-type="message"]`).length > 0) {
                    console.log('Message already exists:', message.id);
                    return;
                }

                console.log('Prepending message:', message.id, message.text);

                const linkedUser = message.user_name ?
                    `<span class="ms-2"><i class="bi bi-link-45deg"></i> ${message.user_name}</span>` :
                    '';

                const username = message.display_name.startsWith('@') ?
                    `<span class="text-primary">@${message.display_name.substring(1)}</span>` :
                    '';

                const replyToMessageId = message.reply_to_message_id || 'N/A';
                const messageGroupId = `reply-${replyToMessageId}`;

                const $messagesContainer = $('#messagesOnlyContainer');
                if ($messagesContainer.length === 0) {
                    console.error('messagesOnlyContainer not found!');
                    return;
                }

                let $messageGroup = $(`#messagesOnlyContainer .message-group[data-message-id="${messageGroupId}"]`);

                if ($messageGroup.length === 0) {
                    console.log('Creating new message group:', messageGroupId);
                    createMessageGroupForReply(replyToMessageId, message);
                    $messageGroup = $(`#messagesOnlyContainer .message-group[data-message-id="${messageGroupId}"]`);
                    if ($messageGroup.length === 0) {
                        console.error('Failed to create message group:', messageGroupId);
                        return;
                    }
                }

                const $responsesList = $messageGroup.find(`.messages-body[data-message-id="${messageGroupId}"]`);
                if ($responsesList.length === 0) {
                    console.error('messages-body not found for:', messageGroupId);
                    console.error('Message group HTML:', $messageGroup.html());
                    return;
                }

                console.log('Found responses list for group:', messageGroupId);

                const isFirstItem = $responsesList.find('.response-item').length === 0;
                const borderClass = isFirstItem ? '' : 'border-top';

                const html = `
            <div class="response-item p-3 ${borderClass} callback-new" data-id="${message.id}" data-type="message">
                <div class="d-flex align-items-start">
                    <div class="avatar me-3 flex-shrink-0">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(message.telegram_full_name)}&background=28a745&color=fff&size=48"
                             class="rounded-circle"
                             style="width: 48px; height: 48px;"
                             alt="${message.telegram_full_name}">
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold text-dark">${message.telegram_full_name}</div>
                                <div class="small text-muted">
                                    ${username}
                                    ${linkedUser}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">${message.created_at.split(' ')[1] || ''}</div>
                                <div class="small text-muted">${message.time_ago}</div>
                            </div>
                        </div>
                        <div class="message-text mt-2 p-2 bg-light rounded">
                            <p class="mb-0">${message.text}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

                $responsesList.prepend(html);
                $messageGroup.prependTo('#messagesOnlyContainer');

                console.log('Message added successfully:', message.id);
                console.log('- Container exists:', $('#messagesOnlyContainer').length > 0);
                console.log('- Message group exists:', $messageGroup.length > 0);
                console.log('- Responses list exists:', $responsesList.length > 0);
                console.log('- Message in DOM:', $(`.response-item[data-id="${message.id}"]`).length > 0);

                updateMessageGroupCount(messageGroupId);
                updateTabCounts();
            }

            function createMessageGroupForReply(replyToMessageId, firstMessage) {
                const $existingGroup = $(`#messagesOnlyContainer .message-group[data-message-id="reply-${replyToMessageId}"]`);
                if ($existingGroup.length > 0) {
                    console.log('Message group already exists:', replyToMessageId);
                    return;
                }

                const $messagesContainer = $('#messagesOnlyContainer');
                if ($messagesContainer.length === 0) {
                    console.error('messagesOnlyContainer not found when creating group!');
                    return;
                }

                const createdDate = firstMessage.created_at || '';
                const timeAgo = firstMessage.time_ago || 'Vừa xong';

                const html = `
            <div class="message-group mb-4" data-message-id="reply-${replyToMessageId}">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-success text-white border-0">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-chat-dots-fill me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold fs-6">Tin nhắn phản hồi</div>
                                    <small class="text-white-50">${createdDate} • ${timeAgo}</small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark message-count">1 tin nhắn</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="responses-list messages-body" data-message-id="reply-${replyToMessageId}">
                        </div>
                    </div>
                </div>
            </div>
        `;

                $messagesContainer.prepend(html);
                $('#emptyRow').remove();
                console.log('Message group created:', replyToMessageId);
            }

            function updateMessageGroupCount(messageId) {
                const messageIdStr = String(messageId);
                const $messageGroups = $(`.message-group[data-message-id="${messageIdStr}"]`);
                $messageGroups.each(function() {
                    const $messageGroup = $(this);
                    const count = $messageGroup.find('.response-item').length;
                    const isMessageGroup = messageIdStr.startsWith('reply-');
                    const countText = isMessageGroup ? `${count} tin nhắn` : `${count} phản hồi`;
                    $messageGroup.find('.message-count').text(countText);
                });
            }

            function updateTabCounts() {
                const callbacksCount = $('#callbacksOnlyContainer .response-item[data-type="callback"]').length;
                const messagesCount = $('#messagesOnlyContainer .response-item[data-type="message"]').length;
                $('#callbacksCount').text(callbacksCount);
                $('#messagesCount').text(messagesCount);
            }

            function showNewCallbacksAlert() {
                $('#newCallbacksCount').text(newCallbacksCount);
                $('#newCallbacksAlert').removeClass('d-none');
            }

            function updateLastUpdateTime() {
                const now = new Date();
                $('#lastUpdate').text('Cập nhật: ' + now.toLocaleTimeString('vi-VN'));
            }

            window.scrollToTop = function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 300);
                $('#newCallbacksAlert').addClass('d-none');
                newCallbacksCount = 0;
            };

            $('#refreshBtn').on('click', function() {
                const $btn = $(this);
                $btn.find('i').addClass('spin');
                fetchNewCallbacks();
                setTimeout(function() {
                    $btn.find('i').removeClass('spin');
                }, 500);
            });

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    stopPolling();
                } else {
                    startPolling();
                    fetchNewCallbacks();
                    fetchNewMessages();
                }
            });

            let currentTab = 'callbacks';
            $('#responseTabs button[data-bs-toggle="tab"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $button = $(this);
                const targetTab = $button.data('bs-target');
                currentTab = targetTab.replace('#', '');

                $('#responseTabs button').removeClass('active').attr('aria-selected', 'false');
                $('.tab-pane').removeClass('show active');

                $button.addClass('active').attr('aria-selected', 'true');
                $(targetTab).addClass('show active');

                updateTabCounts();

                if (currentTab === 'callbacks') {
                    fetchNewCallbacks();
                } else if (currentTab === 'messages') {
                    fetchNewMessages();
                }

                return false;
            });

            updateTabCounts();
            startPolling();
        });
    </script>
    <style>
        .spin {
            animation: spin 0.5s linear;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
