@extends('layouts.coreui')

@section('title', 'Quản lý Commands')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Commands</li>
    </ol>
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-terminal me-2"></i>
                <strong>Danh sách Commands</strong>
            </div>
            <div>
                <a href="{{ route('question-set-commands.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Thêm mới
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Command</th>
                            <th>Bộ Câu Hỏi</th>
                            <th>Tin nhắn phản hồi</th>
                            <th>Trạng thái</th>
                            <th class="text-center" width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commands as $command)
                            <tr>
                                <td><code>{{ $command->command }}</code></td>
                                <td>
                                    <strong>{{ $command->questionSet->name }}</strong>
                                </td>
                                <td>
                                    @if($command->response_message)
                                        <small>{{ Str::limit($command->response_message, 50) }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($command->is_active)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Tắt</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('question-set-commands.edit', $command) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('{{ route('question-set-commands.destroy', $command) }}', 'Bạn có chắc chắn muốn xóa command {{ $command->command }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <h5 class="text-muted">Chưa có command nào</h5>
                                    <a href="{{ route('question-set-commands.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Tạo command đầu tiên
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $commands->links() }}
        </div>
    </div>
@endsection

