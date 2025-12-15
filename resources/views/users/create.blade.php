@extends('layouts.coreui')

@section('title', 'Thêm người dùng mới')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Người dùng</a></li>
    <li class="breadcrumb-item active">Thêm mới</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person-plus me-2"></i>
                <strong>Thêm người dùng mới</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Nhập họ và tên">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="example@email.com">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="form-password">
                                <input type="password" class="form-control  @error('password') is-invalid @enderror" id="password" name="password"  placeholder="Nhập mật khẩu">
                                <button type="button" class="form-password-action" data-coreui-toggle="password" aria-label="Toggle password visibility">
                                  <span class="form-password-action-icon"></span>
                                </button>
                              </div>

                            @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telegram_id" class="form-label">Telegram id <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('telegram_id') is-invalid @enderror" id="telegram_id" name="telegram_id" value="{{ old('telegram_id') }}" placeholder="telegram_id">
                            @error('telegram_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Tạo người dùng
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Hướng dẫn</strong>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">
                    <i class="bi bi-dot"></i> Họ và tên phải có ít nhất 2 ký tự
                </p>
                <p class="text-muted mb-2">
                    <i class="bi bi-dot"></i> Email phải là địa chỉ hợp lệ và chưa tồn tại
                </p>
                <p class="text-muted mb-2">
                    <i class="bi bi-dot"></i> Mật khẩu phải có ít nhất 6 ký tự
                </p>
                <p class="text-muted mb-0">
                    <i class="bi bi-dot"></i> Các trường có dấu <span class="text-danger">*</span> là bắt buộc
                </p>
            </div>
        </div>
    </div> --}}
</div>

@push('scripts')
<script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '-icon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush
@endsection
