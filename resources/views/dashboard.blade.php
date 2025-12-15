@extends('layouts.coreui')

@section('title', 'Dashboard')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row">
    <div class="col-sm-6 col-lg-3">
        <div class="card mb-4 text-white bg-primary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $stats['users'] ?? 0 }}</div>
                    <div>Người dùng</div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('users.index') }}">Xem tất cả</a></li>
                        <li><a class="dropdown-item" href="{{ route('users.create') }}">Thêm mới</a></li>
                    </ul>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px;">
                <div class="d-flex align-items-center justify-content-center h-100">
                    <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card mb-4 text-white bg-success">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $stats['verified'] ?? 0 }}</div>
                    <div>Hoạt động</div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Xem chi tiết</a></li>
                    </ul>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px;">
                <div class="d-flex align-items-center justify-content-center h-100">
                    <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card mb-4 text-white bg-secondary">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $stats['unverified'] ?? 0 }}</div>
                    <div>Không hoạt động</div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Xem chi tiết</a></li>
                    </ul>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px;">
                <div class="d-flex align-items-center justify-content-center h-100">
                    <i class="bi bi-x-circle" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card mb-4 text-white bg-info">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $stats['today'] ?? 0 }}</div>
                    <div>Hôm nay</div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-transparent text-white p-0" type="button" data-coreui-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Xem báo cáo</a></li>
                    </ul>
                </div>
            </div>
            <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px;">
                <div class="d-flex align-items-center justify-content-center h-100">
                    <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-people me-2"></i>
            <strong>Người dùng gần đây</strong>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-list me-1"></i> Xem tất cả
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentUsers ?? [] as $user)
                    <tr>
                        <td><strong>{{ $user->id }}</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md me-3">
                                    <img class="avatar-img" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=321fdb&color=fff" alt="{{ $user->name }}">
                                </div>
                                <div>{{ $user->name }}</div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->status === 'active')
                            <span class="badge bg-success">Hoạt động</span>
                            @else
                            <span class="badge bg-secondary">Không hoạt động</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info" data-coreui-toggle="tooltip" title="Xem">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning" data-coreui-toggle="tooltip" title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted d-block mb-2"></i>
                            <span class="text-muted">Chưa có người dùng nào</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
{{-- <div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>
                <strong>Hành động nhanh</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i> Thêm người dùng mới
                    </a>
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-download me-2"></i> Xuất báo cáo
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-gear me-2"></i> Cài đặt hệ thống
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Thông tin hệ thống</strong>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Laravel Version</span>
                        <span class="badge bg-primary">{{ app()->version() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>PHP Version</span>
                        <span class="badge bg-info">{{ phpversion() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Environment</span>
                        <span class="badge bg-{{ app()->environment('production') ? 'danger' : 'success' }}">{{ app()->environment() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div> --}}
@endsection

