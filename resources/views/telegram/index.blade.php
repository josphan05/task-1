@extends('layouts.coreui')

@section('title', 'Gửi tin nhắn Telegram')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Telegram</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-telegram me-2"></i>
                    <strong>Gửi tin nhắn Telegram</strong>
                </div>
                <a href="{{ route('telegram.responses') }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-reply-all me-1"></i> Xem phản hồi
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('telegram.send') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-diagram-3 me-1"></i> Kiểu gửi <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="target_type"
                                       id="target_type_users"
                                       value="users"
                                       {{ old('target_type', 'users') === 'users' ? 'checked' : '' }}>
                                <label class="form-check-label" for="target_type_users">
                                    Gửi cho người dùng
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="target_type"
                                       id="target_type_chatgroup"
                                       value="chatgroup"
                                       {{ old('target_type') === 'chatgroup' ? 'checked' : '' }}>
                                <label class="form-check-label" for="target_type_chatgroup">
                                    Gửi vào nhóm
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div id="user-select-wrapper">
                            @if($users->count() > 0)
                            @php
                                $userOptions = $users->map(function($user) {
                                    return [
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=321fdb&color=fff&size=32',
                                        'meta' => $user->telegram_id,
                                    ];
                                })->toArray();
                            @endphp

                            <x-select2
                                name="user_ids"
                                id="user_ids"
                                label="Chọn người nhận"
                                :options="$userOptions"
                                :multiple="true"
                                :show-select-all="true"
                                :required="true"
                                placeholder="Tìm kiếm và chọn người nhận..."
                            />

                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Đã chọn: <strong id="selectedCount">0</strong> / {{ $users->count() }} người dùng
                            </div>
                            @else
                            <label class="form-label fw-semibold">
                                <i class="bi bi-people me-1"></i> Chọn người nhận <span class="text-danger">*</span>
                            </label>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Không có người dùng nào có Telegram ID.
                                <a href="{{ route('users.index') }}">Cập nhật thông tin người dùng</a>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- Nội dung tin nhắn -->
                    <div class="mb-4">
                        <label for="message" class="form-label fw-semibold">
                            <i class="bi bi-chat-text me-1"></i> Nội dung tin nhắn <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('message') is-invalid @enderror"
                                  id="message"
                                  name="message"
                                  rows="6"
                                  placeholder="Nhập nội dung tin nhắn..."
                                  maxlength="4096">{{ old('message') }}</textarea>
                        @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Inline Keyboard Buttons -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0">
                                <i class="bi bi-grid-3x2 me-1"></i> Nút tương tác (tuỳ chọn)
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addButtonRow">
                                <i class="bi bi-plus-lg me-1"></i> Thêm hàng nút
                            </button>
                        </div>
                        <div class="form-text mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Thêm các nút bấm dưới tin nhắn. Mỗi hàng có thể chứa nhiều nút.
                        </div>

                        <div id="buttonRowsContainer">
                            <!-- Button rows will be added here dynamically -->
                        </div>

                        <!-- Preview -->
                        <div id="buttonPreview" class="mt-3 d-none">
                            <label class="form-label fw-semibold text-muted small">
                                <i class="bi bi-eye me-1"></i> Xem trước
                            </label>
                            <div class="border rounded p-3 bg-light">
                                <div id="previewContent" class="mb-2 text-dark" style="white-space: pre-wrap;">Nội dung tin nhắn...</div>
                                <div id="previewButtons" class="d-flex flex-column gap-1">
                                    <!-- Preview buttons will be rendered here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Gửi tin nhắn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar with quick templates -->
    <div class="col-lg-4">
        <!-- Quick Callback Templates -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>
                <strong>Mẫu nút nhanh</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Click để thêm nhanh các mẫu nút phổ biến:</p>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm quick-template" data-template="confirm">
                        <i class="bi bi-check-circle me-1"></i> Xác nhận / Huỷ
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm quick-template" data-template="rating">
                        <i class="bi bi-star me-1"></i> Đánh giá (1-5 sao)
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm quick-template" data-template="yesno">
                        <i class="bi bi-question-circle me-1"></i> Có / Không
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm quick-template" data-template="poll">
                        <i class="bi bi-list-check me-1"></i> Khảo sát A/B/C
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm quick-template" data-template="feedback">
                        <i class="bi bi-emoji-smile me-1"></i> Phản hồi cảm xúc
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Responses -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-clock-history me-2"></i>
                    <strong>Phản hồi gần đây</strong>
                </div>

            </div>
            <div class="card-body p-0">
                <div id="recentCallbacks" class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-hourglass-split me-1"></i> Đang tải...
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('telegram.responses') }}" class="btn btn-sm btn-link">
                    Xem tất cả <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function () {
        const $form = $('form[action="{{ route('telegram.send') }}"]');
        const $userIds = $('#user_ids');
        const $userSelectWrapper = $('#user-select-wrapper');
        const $buttonRowsContainer = $('#buttonRowsContainer');
        const $buttonPreview = $('#buttonPreview');
        const $previewButtons = $('#previewButtons');
        const $previewContent = $('#previewContent');
        const $messageTextarea = $('#message');

        let rowIndex = 0;

        function toggleUserSelect() {
            const targetType = $('input[name="target_type"]:checked').val();

            if (targetType === 'chatgroup') {
                $userSelectWrapper.slideUp(100);
                $userIds.prop('required', false).val(null).trigger('change');
                $userIds.removeClass('is-invalid');
                $userIds.closest('.coreui-multi-select-wrapper').find('.invalid-feedback').remove();
                $userIds.valid();
            } else {
                $userSelectWrapper.slideDown(100);
                $userIds.prop('required', true);
            }
        }

        // Button row template
        function createButtonRow(index) {
            return `
                <div class="button-row card mb-2" data-row="${index}">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary">Hàng ${index + 1}</span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-success add-button-to-row" title="Thêm nút">
                                    <i class="bi bi-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger remove-row" title="Xoá hàng">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="buttons-in-row d-flex flex-wrap gap-2">
                            ${createButtonItem(index, 0)}
                        </div>
                    </div>
                </div>
            `;
        }

        // Single button template
        function createButtonItem(rowIndex, buttonIndex) {
            return `
                <div class="button-item border rounded p-2 bg-white" style="min-width: 200px; flex: 1;" data-button="${buttonIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Nút ${buttonIndex + 1}</span>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-button" title="Xoá nút">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <input type="text"
                               class="form-control form-control-sm button-text"
                               name="buttons[${rowIndex}][${buttonIndex}][text]"
                               placeholder="Tên nút"
                               maxlength="64">
                    </div>
                    <div class="mb-2">
                        <select class="form-select form-select-sm button-type" name="buttons[${rowIndex}][${buttonIndex}][type]">
                            <option value="url">🔗 URL Link</option>
                            <option value="callback">📩 Callback</option>
                        </select>
                    </div>
                    <div class="button-value-wrapper">
                        <input type="text"
                               class="form-control form-control-sm button-value"
                               name="buttons[${rowIndex}][${buttonIndex}][value]"
                               placeholder="https://example.com"
                               maxlength="256">
                    </div>
                </div>
            `;
        }

        // Add new button row
        $('#addButtonRow').on('click', function() {
            $buttonRowsContainer.append(createButtonRow(rowIndex));
            rowIndex++;
            updatePreview();
            $buttonPreview.removeClass('d-none');
        });

        // Add button to existing row
        $buttonRowsContainer.on('click', '.add-button-to-row', function() {
            const $row = $(this).closest('.button-row');
            const rowIdx = $row.data('row');
            const $buttonsContainer = $row.find('.buttons-in-row');
            const buttonIdx = $buttonsContainer.find('.button-item').length;

            if (buttonIdx >= 8) {
                alert('Mỗi hàng chỉ được tối đa 8 nút!');
                return;
            }

            $buttonsContainer.append(createButtonItem(rowIdx, buttonIdx));
            updatePreview();
        });

        // Remove button row
        $buttonRowsContainer.on('click', '.remove-row', function() {
            $(this).closest('.button-row').fadeOut(200, function() {
                $(this).remove();
                updatePreview();
                if ($buttonRowsContainer.find('.button-row').length === 0) {
                    $buttonPreview.addClass('d-none');
                }
            });
        });

        // Remove single button
        $buttonRowsContainer.on('click', '.remove-button', function() {
            const $row = $(this).closest('.button-row');
            const $buttonsContainer = $row.find('.buttons-in-row');

            if ($buttonsContainer.find('.button-item').length <= 1) {
                // If only one button left, remove entire row
                $row.fadeOut(200, function() {
                    $(this).remove();
                    updatePreview();
                    if ($buttonRowsContainer.find('.button-row').length === 0) {
                        $buttonPreview.addClass('d-none');
                    }
                });
            } else {
                $(this).closest('.button-item').fadeOut(200, function() {
                    $(this).remove();
                    updatePreview();
                });
            }
        });

        // Change button type placeholder
        $buttonRowsContainer.on('change', '.button-type', function() {
            const type = $(this).val();
            const $valueInput = $(this).closest('.button-item').find('.button-value');

            if (type === 'url') {
                $valueInput.attr('placeholder', 'https://example.com');
            } else {
                $valueInput.attr('placeholder', 'callback_data_value');
            }
            updatePreview();
        });

        // Update preview on input changes
        $buttonRowsContainer.on('input', '.button-text, .button-value', function() {
            updatePreview();
        });

        $messageTextarea.on('input', function() {
            updatePreview();
        });

        // Update preview display
        function updatePreview() {
            const message = $messageTextarea.val() || 'Nội dung tin nhắn...';
            $previewContent.text(message);

            let previewHtml = '';

            $buttonRowsContainer.find('.button-row').each(function() {
                let rowHtml = '<div class="d-flex gap-1 flex-wrap">';

                $(this).find('.button-item').each(function() {
                    const text = $(this).find('.button-text').val() || 'Nút';
                    const type = $(this).find('.button-type').val();
                    const icon = type === 'url' ? '🔗' : '📩';

                    rowHtml += `<button type="button" class="btn btn-sm btn-primary flex-fill" style="max-width: 200px;" disabled>
                        ${icon} ${text}
                    </button>`;
                });

                rowHtml += '</div>';
                previewHtml += rowHtml;
            });

            $previewButtons.html(previewHtml);
        }

        $('input[name="target_type"]').on('change', function() {
            toggleUserSelect();
        });

        toggleUserSelect();

        window.ValidationConfig.init($form, {
            rules: {
                target_type: { required: true },
                user_ids: {
                    required: {
                        depends: function() {
                            return $('input[name="target_type"]:checked').val() === 'users';
                        }
                    }
                },
                message: {
                    required: true,
                    minlength: 1,
                    maxlength: 4096
                }
            },
            messages: {
                target_type: 'Vui lòng chọn kiểu gửi.',
                user_ids: {
                    required: 'Vui lòng chọn ít nhất một người nhận.'
                },
                message: {
                    required: 'Vui lòng nhập nội dung tin nhắn.',
                    minlength: 'Nội dung tin nhắn không được để trống.',
                    maxlength: 'Nội dung tin nhắn không được vượt quá 4096 ký tự.'
                }
            }
        });

        // Quick templates
        const templates = {
            confirm: [
                [
                    { text: '✅ Xác nhận', type: 'callback', value: 'confirm_yes' },
                    { text: '❌ Huỷ bỏ', type: 'callback', value: 'confirm_no' }
                ]
            ],
            rating: [
                [
                    { text: '⭐', type: 'callback', value: 'rating_1' },
                    { text: '⭐⭐', type: 'callback', value: 'rating_2' },
                    { text: '⭐⭐⭐', type: 'callback', value: 'rating_3' },
                    { text: '⭐⭐⭐⭐', type: 'callback', value: 'rating_4' },
                    { text: '⭐⭐⭐⭐⭐', type: 'callback', value: 'rating_5' }
                ]
            ],
            yesno: [
                [
                    { text: '👍 Có', type: 'callback', value: 'answer_yes' },
                    { text: '👎 Không', type: 'callback', value: 'answer_no' }
                ]
            ],
            poll: [
                [
                    { text: '🅰️ Phương án A', type: 'callback', value: 'poll_a' }
                ],
                [
                    { text: '🅱️ Phương án B', type: 'callback', value: 'poll_b' }
                ],
                [
                    { text: '©️ Phương án C', type: 'callback', value: 'poll_c' }
                ]
            ],
            feedback: [
                [
                    { text: '😍 Rất hài lòng', type: 'callback', value: 'feedback_5' },
                    { text: '😊 Hài lòng', type: 'callback', value: 'feedback_4' }
                ],
                [
                    { text: '😐 Bình thường', type: 'callback', value: 'feedback_3' },
                    { text: '😕 Không hài lòng', type: 'callback', value: 'feedback_2' }
                ]
            ]
        };

        // Apply template
        $('.quick-template').on('click', function() {
            const templateName = $(this).data('template');
            const template = templates[templateName];

            if (!template) return;

            // Clear existing buttons
            $buttonRowsContainer.empty();
            rowIndex = 0;

            // Add template buttons
            template.forEach(function(row, rIdx) {
                const rowHtml = `
                    <div class="button-row card mb-2" data-row="${rIdx}">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary">Hàng ${rIdx + 1}</span>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-success add-button-to-row" title="Thêm nút">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger remove-row" title="Xoá hàng">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="buttons-in-row d-flex flex-wrap gap-2">
                            </div>
                        </div>
                    </div>
                `;
                $buttonRowsContainer.append(rowHtml);

                const $buttonsContainer = $buttonRowsContainer.find(`.button-row[data-row="${rIdx}"] .buttons-in-row`);

                row.forEach(function(btn, bIdx) {
                    const buttonHtml = createButtonItem(rIdx, bIdx);
                    $buttonsContainer.append(buttonHtml);

                    // Set values
                    const $item = $buttonsContainer.find(`.button-item[data-button="${bIdx}"]`);
                    $item.find('.button-text').val(btn.text);
                    $item.find('.button-type').val(btn.type);
                    $item.find('.button-value').val(btn.value);

                    if (btn.type === 'callback') {
                        $item.find('.button-value').attr('placeholder', 'callback_data_value');
                    }
                });

                rowIndex++;
            });

            $buttonPreview.removeClass('d-none');
            updatePreview();
        });

        // Load recent callbacks
        function loadRecentCallbacks() {
            $.ajax({
                url: '{{ route("telegram.callbacks") }}',
                method: 'GET',
                data: { limit: 10 },
                success: function(response) {
                    if (response.success) {
                        renderRecentCallbacks(response.data);
                    }
                }
            });
        }

        function renderRecentCallbacks(callbacks) {
            const $container = $('#recentCallbacks');

            if (callbacks.length === 0) {
                $container.html(`
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-inbox"></i> Chưa có phản hồi
                    </div>
                `);
                return;
            }

            let html = '';
            callbacks.forEach(function(cb) {
                html += `
                    <div class="list-group-item py-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex: 1;">
                                ${cb.message_text ? `
                                    <div class="small text-muted mb-1" style="font-style: italic;">
                                        <i class="bi bi-quote"></i> ${cb.message_text.length > 60 ? cb.message_text.substring(0, 60) + '...' : cb.message_text}
                                    </div>
                                ` : ''}
                                <span class="badge bg-primary">${cb.callback_data}</span>
                                <div class="small text-muted mt-1">${cb.display_name}</div>
                            </div>
                            <small class="text-muted">${cb.time_ago}</small>
                        </div>
                    </div>
                `;
            });

            $container.html(html);
        }

        // Poll for new callbacks every 5 seconds
        let recentLatestId = 0;
        function pollRecentCallbacks() {
            $.ajax({
                url: '{{ route("telegram.callbacks.new") }}',
                method: 'GET',
                data: { since_id: recentLatestId },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        recentLatestId = response.latest_id;
                        loadRecentCallbacks();
                    }
                }
            });
        }

        // Initial load
        loadRecentCallbacks();

        // Poll every 5 seconds
        setInterval(pollRecentCallbacks, 5000);
    });
</script>
@endpush
