@extends('layouts.coreui')

@section('title', 'Tạo Bộ Câu Hỏi Mới')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('question-sets.index') }}">Bộ Câu Hỏi</a></li>
        <li class="breadcrumb-item active">Tạo mới</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-2"></i>
                    <strong>Tạo Bộ Câu Hỏi Mới</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('question-sets.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên bộ câu hỏi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" placeholder="Ví dụ: Form Phản Ánh">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                name="description" rows="3" placeholder="Mô tả về bộ câu hỏi này">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="start_message" class="form-label">Tin nhắn bắt đầu</label>
                            <textarea class="form-control @error('start_message') is-invalid @enderror" id="start_message"
                                name="start_message" rows="2" placeholder="Ví dụ: Xin chào! Tôi là bot hỗ trợ. Hãy trả lời các câu hỏi sau:">{{ old('start_message') }}</textarea>
                            <small class="text-muted">Để trống sẽ dùng tin nhắn mặc định</small>
                            @error('start_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="completion_message" class="form-label">Tin nhắn hoàn thành</label>
                            <textarea class="form-control @error('completion_message') is-invalid @enderror" id="completion_message"
                                name="completion_message" rows="2" placeholder="Ví dụ: Cảm ơn, thông tin đã được ghi nhận.">{{ old('completion_message') }}</textarea>
                            <small class="text-muted">Để trống sẽ dùng tin nhắn mặc định</small>
                            @error('completion_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
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
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('is_default') is-invalid @enderror" type="checkbox"
                                        id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        Đặt làm mặc định
                                    </label>
                                </div>
                                @error('is_default')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Tạo bộ câu hỏi
                            </button>
                            <a href="{{ route('question-sets.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

