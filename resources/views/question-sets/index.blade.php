@extends('layouts.coreui')

@section('title', 'Quản lý Bộ Câu Hỏi')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Bộ Câu Hỏi</li>
    </ol>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-question-circle me-2"></i>
                <strong>Danh sách Bộ Câu Hỏi</strong>
            </div>
            <div>
                <a href="{{ route('question-sets.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Thêm mới
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Số câu hỏi</th>
                            <th>Trạng thái</th>
                            <th>Mặc định</th>
                            <th class="text-center" width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questionSets as $questionSet)
                            <tr>
                                <td><strong>#{{ $questionSet->id }}</strong></td>
                                <td>
                                    <div class="fw-semibold">{{ $questionSet->name }}</div>
                                    @if($questionSet->description)
                                        <small class="text-muted">{{ Str::limit($questionSet->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $questionSet->questions->count() }} câu</span>
                                </td>
                                <td>
                                    @if($questionSet->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Tắt</span>
                                    @endif
                                </td>
                                <td>
                                    @if($questionSet->is_default)
                                        <span class="badge bg-primary">Mặc định</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('question-sets.show', $questionSet) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('question-sets.edit', $questionSet) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('{{ route('question-sets.destroy', $questionSet) }}', 'Bạn có chắc chắn muốn xóa bộ câu hỏi {{ $questionSet->name }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <h5 class="text-muted">Chưa có bộ câu hỏi nào</h5>
                                    <a href="{{ route('question-sets.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Tạo bộ câu hỏi đầu tiên
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $questionSets->links() }}
        </div>
    </div>
@endsection

