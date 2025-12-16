@extends('layouts.coreui')

@section('title', 'Chi tiết người dùng')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Người dùng</a></li>
        <li class="breadcrumb-item active">Chi tiết</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl mb-3">
                        <img class="avatar-img rounded-circle"
                            src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=128&background=321fdb&color=fff"
                            alt="{{ $user->name }}">
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>

                    @if ($user->status === 'active')
                        <span class="badge bg-success mb-3">
                            <i class="bi bi-check-circle me-1"></i> Hoạt động
                        </span>
                    @else
                        <span class="badge bg-secondary mb-3">
                            <i class="bi bi-x-circle me-1"></i> Không hoạt động
                        </span>
                    @endif

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                        </a>
                        <a href="mailto:{{ $user->email }}" class="btn btn-outline-secondary">
                            <i class="bi bi-envelope me-1"></i> Gửi email
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Thông tin chi tiết</strong>
                </div>
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <th width="200">ID</th>
                                <td><strong>#{{ $user->id }}</strong></td>
                            </tr>
                            <tr>
                                <th>Họ và tên</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>
                                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                </td>
                            </tr>
                            <tr>
                                <th>Trạng thái</th>
                                <td>
                                    @if ($user->status === 'active')
                                        <span class="text-success">
                                            <i class="bi bi-check-circle me-1"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="text-secondary">
                                            <i class="bi bi-x-circle me-1"></i> Không hoạt động
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Ngày tạo</th>
                                <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Cập nhật lần cuối</th>
                                <td>{{ $user->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>
                    <strong>Lịch sử hoạt động</strong>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-badge bg-primary">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <strong>Tài khoản được tạo</strong>
                                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="text-muted mb-0">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if ($user->email_verified_at)
                            <div class="timeline-item">
                                <div class="timeline-badge bg-success">
                                    <i class="bi bi-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <strong>Email đã được xác thực</strong>
                                        <small class="text-muted">{{ $user->email_verified_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="text-muted mb-0">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($user->updated_at != $user->created_at)
                            <div class="timeline-item">
                                <div class="timeline-badge bg-info">
                                    <i class="bi bi-pencil"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <strong>Thông tin được cập nhật</strong>
                                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="text-muted mb-0">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    @push('styles')
        <style>
            .timeline {
                position: relative;
                padding-left: 30px;
            }

            .timeline-item {
                position: relative;
                padding-bottom: 20px;
                border-left: 2px solid #dee2e6;
                padding-left: 20px;
                margin-left: 10px;
            }

            .timeline-item:last-child {
                border-left: 0;
                padding-bottom: 0;
            }

            .timeline-badge {
                position: absolute;
                left: -32px;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 12px;
            }

            .timeline-content {
                background: #f8f9fa;
                padding: 10px 15px;
                border-radius: 5px;
            }
        </style>
    @endpush
@endsection
