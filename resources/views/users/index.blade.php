@extends('layouts.coreui')

@section('title', 'Danh sách người dùng')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Người dùng</li>
    </ol>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-people me-2"></i>
                <strong>Danh sách người dùng</strong>
            </div>
            <div>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Thêm mới
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Form -->
            <form action="{{ route('users.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">-- Trạng thái --</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Không hoạt động
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-filter me-1"></i> Lọc
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>

            <!-- Users Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="60">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                </div>
                            </th>
                            <th>ID</th>
                            <th>Người dùng</th>
                            <th>Email</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-center" width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input row-checkbox" type="checkbox"
                                            value="{{ $user->id }}">
                                    </div>
                                </td>
                                <td><strong>#{{ $user->id }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md me-3">
                                            <img class="avatar-img"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=321fdb&color=fff"
                                                alt="{{ $user->name }}">
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <small class="text-muted">ID: {{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                        {{ $user->email }}
                                    </a>
                                </td>
                                <td>
                                    @if ($user->status === 'active')
                                        <span class="badge rounded-pill bg-success">
                                            <i class="bi bi-check-circle me-1"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary">
                                            <i class="bi bi-x-circle me-1"></i> Không hoạt động
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $user->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info"
                                            data-coreui-toggle="tooltip" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning"
                                            data-coreui-toggle="tooltip" title="Chỉnh sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-coreui-toggle="tooltip"
                                            title="Xóa"
                                            onclick="confirmDelete('{{ route('users.destroy', $user) }}', 'Bạn có chắc chắn muốn xóa người dùng {{ $user->name }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <h5 class="text-muted">Không có dữ liệu</h5>
                                    <p class="text-muted mb-3">Chưa có người dùng nào trong hệ thống</p>
                                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Thêm người dùng đầu tiên
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Hiển thị {{ $users->firstItem() }} - {{ $users->lastItem() }} / {{ $users->total() }} kết quả
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // Check all functionality
            document.getElementById('checkAll').addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        </script>
    @endpush
@endsection
