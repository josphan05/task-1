@extends('layouts.coreui')

@section('title', 'Thêm Câu Hỏi')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('question-sets.index') }}">Bộ Câu Hỏi</a></li>
        <li class="breadcrumb-item"><a href="{{ route('question-sets.show', $questionSet) }}">{{ $questionSet->name }}</a></li>
        <li class="breadcrumb-item active">Thêm câu hỏi</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-plus-circle me-2"></i>
                    <strong>Thêm Câu Hỏi Mới</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('question-sets.questions.store', $questionSet) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="question_text" class="form-label">Nội dung câu hỏi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('question_text') is-invalid @enderror" id="question_text"
                                name="question_text" rows="3" placeholder="Ví dụ: Bạn tên gì?">{{ old('question_text') }}</textarea>
                            @error('question_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="field_name" class="form-label">Tên field <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('field_name') is-invalid @enderror" id="field_name"
                                    name="field_name" value="{{ old('field_name') }}" placeholder="Ví dụ: name, phone, email">
                                <small class="text-muted">Tên để lưu trữ câu trả lời (không dấu, không khoảng trắng)</small>
                                @error('field_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="order" class="form-label">Thứ tự <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('order') is-invalid @enderror" id="order"
                                    name="order" value="{{ old('order', $questionSet->questions->count() + 1) }}" min="1">
                                @error('order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="validation_rule" class="form-label">Rule validation</label>
                            <select class="form-select @error('validation_rule') is-invalid @enderror" id="validation_rule"
                                name="validation_rule">
                                <option value="">-- Không validate --</option>
                                <option value="phone" {{ old('validation_rule') == 'phone' ? 'selected' : '' }}>Số điện thoại (0xxxxxxxxx)</option>
                                <option value="email" {{ old('validation_rule') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="numeric" {{ old('validation_rule') == 'numeric' ? 'selected' : '' }}>Số</option>
                                <option value="min:3" {{ old('validation_rule') == 'min:3' ? 'selected' : '' }}>Tối thiểu 3 ký tự</option>
                                <option value="min:5" {{ old('validation_rule') == 'min:5' ? 'selected' : '' }}>Tối thiểu 5 ký tự</option>
                                <option value="min:10" {{ old('validation_rule') == 'min:10' ? 'selected' : '' }}>Tối thiểu 10 ký tự</option>
                            </select>
                            @error('validation_rule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="error_message" class="form-label">Thông báo lỗi</label>
                            <input type="text" class="form-control @error('error_message') is-invalid @enderror" id="error_message"
                                name="error_message" value="{{ old('error_message') }}" placeholder="Ví dụ: Số điện thoại không hợp lệ">
                            <small class="text-muted">Thông báo hiển thị khi validation fail</small>
                            @error('error_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input @error('is_required') is-invalid @enderror" type="checkbox"
                                    id="is_required" name="is_required" value="1" {{ old('is_required', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_required">
                                    Câu hỏi bắt buộc
                                </label>
                            </div>
                            @error('is_required')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="options" class="form-label">Inline Keyboard Options</label>
                            <textarea class="form-control @error('options') is-invalid @enderror" id="options"
                                name="options" rows="5" placeholder='Mỗi dòng một option, format: "Text|Value"&#10;Ví dụ:&#10;Có|yes&#10;Không|no&#10;Hoặc nhiều dòng cho nhiều nút:&#10;Option 1|value1&#10;Option 2|value2&#10;Option 3|value3'>{{ old('options') }}</textarea>
                            <small class="text-muted">
                                Format: Mỗi dòng một option, dạng "Text|Value". Nếu có options, user sẽ chọn từ inline keyboard thay vì nhập text.
                                <br>Ví dụ: Có|yes (một nút) hoặc nhiều dòng cho nhiều nút
                            </small>
                            @error('options')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Thêm câu hỏi
                            </button>
                            <a href="{{ route('question-sets.show', $questionSet) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

