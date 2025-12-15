@extends('layouts.coreui')

@section('title', 'Chỉnh sửa người dùng')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Người dùng</a></li>
    <li class="breadcrumb-item active">Chỉnh sửa</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-pencil-square me-2"></i>
                <strong>Chỉnh sửa người dùng: {{ $user->name }}</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Để trống nếu không đổi">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                            @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Để trống nếu không muốn thay đổi</small>
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="telegram_id" class="form-label">Telegram id <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('telegram_id') is-invalid @enderror" id="telegram_id" name="telegram_id" value="{{ old('telegram_id', $user->telegram_id) }}" placeholder="telegram_id">
                            @error('telegram_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Cập nhật
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-person me-2"></i>
                <strong>Thông tin</strong>
            </div>
            <div class="card-body text-center">
                <div class="avatar avatar-xl mb-3">
                    <img class="avatar-img" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=128&background=321fdb&color=fff" alt="{{ $user->name }}">
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>

                @if($user->status === 'active')
                <span class="badge bg-success">
                    <i class="bi bi-check-circle me-1"></i> Hoạt động
                </span>
                @else
                <span class="badge bg-secondary">
                    <i class="bi bi-x-circle me-1"></i> Không hoạt động
                </span>
                @endif
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>Ngày tạo</span>
                    <span>{{ $user->created_at->format('d/m/Y H:i') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Cập nhật lần cuối</span>
                    <span>{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                </li>
            </ul>
        </div>

        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Vùng nguy hiểm</strong>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Xóa người dùng này sẽ không thể khôi phục.</p>
                <button type="button" class="btn btn-danger w-100"
                        onclick="confirmDelete('{{ route('users.destroy', $user) }}', 'Bạn có chắc chắn muốn xóa người dùng {{ $user->name }}?')">
                    <i class="bi bi-trash me-1"></i> Xóa người dùng
                </button>
            </div>
        </div>
    </div>
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
