@extends('layouts.coreui')

@section('title', $questionSet->name)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('question-sets.index') }}">Bộ Câu Hỏi</a></li>
        <li class="breadcrumb-item active">{{ $questionSet->name }}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-question-circle me-2"></i>
                        <strong>{{ $questionSet->name }}</strong>
                    </div>
                    <div>
                        <a href="{{ route('question-sets.edit', $questionSet) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($questionSet->description)
                        <p class="text-muted">{{ $questionSet->description }}</p>
                    @endif

                    <div class="mb-3">
                        <strong>Tin nhắn bắt đầu:</strong>
                        <p class="text-muted">{{ $questionSet->start_message ?: 'Mặc định' }}</p>
                    </div>

                    <div class="mb-3">
                        <strong>Tin nhắn hoàn thành:</strong>
                        <p class="text-muted">{{ $questionSet->completion_message ?: 'Mặc định' }}</p>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        @if($questionSet->is_active)
                            <span class="badge bg-success">Hoạt động</span>
                        @else
                            <span class="badge bg-secondary">Tắt</span>
                        @endif
                        @if($questionSet->is_default)
                            <span class="badge bg-primary">Mặc định</span>
                        @endif
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Câu hỏi ({{ $questionSet->questions->count() }})</h5>
                        <a href="{{ route('question-sets.questions.create', $questionSet) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Thêm câu hỏi
                        </a>
                    </div>

                    @if($questionSet->questions->count() > 0)
                        <div class="list-group">
                            @foreach($questionSet->questions->sortBy('order') as $question)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-secondary">#{{ $question->order }}</span>
                                                <span class="badge bg-info">{{ $question->field_name }}</span>
                                                @if($question->is_required)
                                                    <span class="badge bg-danger">Bắt buộc</span>
                                                @endif
                                                @if($question->validation_rule)
                                                    <span class="badge bg-warning">{{ $question->validation_rule }}</span>
                                                @endif
                                            </div>
                                            <p class="mb-1"><strong>{{ $question->question_text }}</strong></p>
                                            @if($question->options && count($question->options) > 0)
                                                <div class="mt-2">
                                                    <small class="text-info">
                                                        <i class="bi bi-keyboard"></i> Inline Keyboard:
                                                        @foreach($question->options as $opt)
                                                            <span class="badge bg-secondary">{{ $opt['text'] }}</span>
                                                        @endforeach
                                                    </small>
                                                </div>
                                            @endif
                                            @if($question->error_message)
                                                <small class="text-muted d-block mt-1">Lỗi: {{ $question->error_message }}</small>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('question-sets.questions.edit', [$questionSet, $question]) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete('{{ route('question-sets.questions.destroy', [$questionSet, $question]) }}', 'Bạn có chắc chắn muốn xóa câu hỏi này?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">Chưa có câu hỏi nào</p>
                            <a href="{{ route('question-sets.questions.create', $questionSet) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Thêm câu hỏi đầu tiên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

