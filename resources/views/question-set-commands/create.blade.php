@extends('layouts.coreui')

@section('title', 'Tạo Command Mới')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('question-set-commands.index') }}">Commands</a></li>
        <li class="breadcrumb-item active">Tạo mới</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-2"></i>
                    <strong>Tạo Command Mới</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('question-set-commands.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="command" class="form-label">Command <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">/</span>
                                <input type="text" class="form-control @error('command') is-invalid @enderror" id="command"
                                    name="command" value="{{ old('command') }}" placeholder="start, survey, feedback">
                            </div>
                            <small class="text-muted">Command sẽ tự động thêm dấu / ở đầu (ví dụ: start → /start)</small>
                            @error('command')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="question_set_id" class="form-label">Bộ Câu Hỏi <span class="text-danger">*</span></label>
                            <select class="form-select @error('question_set_id') is-invalid @enderror" id="question_set_id"
                                name="question_set_id">
                                <option value="">-- Chọn bộ câu hỏi --</option>
                                @foreach($questionSets as $qs)
                                    <option value="{{ $qs->id }}" {{ old('question_set_id') == $qs->id ? 'selected' : '' }}>
                                        {{ $qs->name }} @if($qs->is_default)(Mặc định)@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('question_set_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="response_message" class="form-label">Tin nhắn phản hồi</label>
                            <textarea class="form-control @error('response_message') is-invalid @enderror" id="response_message"
                                name="response_message" rows="3" placeholder="Tin nhắn sẽ gửi khi user gõ command này">{{ old('response_message') }}</textarea>
                            <small class="text-muted">Để trống nếu không cần tin nhắn phản hồi trước khi bắt đầu form</small>
                            @error('response_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox"
                                    id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Hoạt động
                                </label>
                            </div>
                            @error('is_active')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Tạo command
                            </button>
                            <a href="{{ route('question-set-commands.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

