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
            <div class="card-header">
                <i class="bi bi-telegram me-2"></i>
                <strong>Gửi tin nhắn Telegram</strong>
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
</div>

@endsection

@push('scripts')
<script>
    $(function () {
        const $form = $('form[action="{{ route('telegram.send') }}"]');
        const $userIds = $('#user_ids');
        const $userSelectWrapper = $('#user-select-wrapper');

        function toggleUserSelect() {
            const targetType = $('input[name="target_type"]:checked').val();

            if (targetType === 'chatgroup') {
                $userSelectWrapper.slideUp(100);
                $userIds.prop('required', false).val(null).trigger('change');
                $userIds.removeClass('is-invalid');
                $userIds.closest('.select2-wrapper').find('.invalid-feedback').remove();
                $userIds.valid();
            } else {
                $userSelectWrapper.slideDown(100);
                $userIds.prop('required', true);
            }
        }

        $('input[name="target_type"]').on('change', function() {
            toggleUserSelect();
        });

        toggleUserSelect();

        $form.validate({
            onfocusout: function (element) {
                this.element(element);
            },
            onkeyup: false,
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
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
            errorPlacement: function (error, element) {
                if (element.attr('type') === 'radio') {
                    error.appendTo(element.closest('.mb-4'));
                } else if (element.attr('id') === 'user_ids' || element.hasClass('form-multi-select')) {
                    const $wrapper = element.closest('.select2-wrapper');
                    if ($wrapper.length) {
                        error.insertAfter($wrapper);
                    } else {
                        error.insertAfter(element.closest('.select2-container').parent() || element);
                    }
                } else {
                    error.insertAfter(element);
                }
            }
        });
    });
</script>
@endpush
