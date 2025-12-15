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
                <form action="{{ route('telegram.send') }}" method="POST">
                    @csrf
                    <div class="mb-4">
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
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Không có người dùng nào có Telegram ID.
                            <a href="{{ route('users.index') }}">Cập nhật thông tin người dùng</a>
                        </div>
                        @endif

                        @error('user_ids')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
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
                        <div class="form-text">
                            <span id="charCount">0</span>/4096 ký tự. Hỗ trợ HTML formatting.
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" {{ $users->count() == 0 ? 'disabled' : '' }}>
                            <i class="bi bi-send me-1"></i> Gửi tin nhắn
                        </button>
                        <button type="button" class="btn btn-secondary" id="resetBtn">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Làm mới
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('user_ids');
    const selectedCountEl = document.getElementById('selectedCount');

    // Update selected count
    if (select) {
        select.addEventListener('change', function() {
            const selected = Array.from(this.selectedOptions).map(opt => opt.value);
            if (selectedCountEl) {
                selectedCountEl.textContent = selected.length;
            }
        });

        // Initial count
        const selected = Array.from(select.selectedOptions).map(opt => opt.value);
        if (selectedCountEl) {
            selectedCountEl.textContent = selected.length;
        }
    }

    // Reset button
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (select) {
                Array.from(select.options).forEach(opt => opt.selected = false);
                select.dispatchEvent(new Event('change'));
            }
            const messageInput = document.getElementById('message');
            if (messageInput) {
                messageInput.value = '';
                const charCount = document.getElementById('charCount');
                if (charCount) {
                    charCount.textContent = '0';
                }
            }
        });
    }

    // Character count
    const messageInput = document.getElementById('message');
    const charCount = document.getElementById('charCount');

    if (messageInput && charCount) {
        messageInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            charCount.classList.toggle('text-danger', this.value.length > 4000);
        });
        charCount.textContent = messageInput.value.length;
    }
});
</script>
@endpush
@endsection
