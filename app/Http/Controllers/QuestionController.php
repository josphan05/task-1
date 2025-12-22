<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Services\QuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function __construct(
        protected QuestionService $questionService
    ) {}

    public function create(QuestionSet $questionSet): View
    {
        return view('questions.create', compact('questionSet'));
    }

    public function store(StoreQuestionRequest $request, QuestionSet $questionSet): RedirectResponse
    {
        $data = $request->validated();
        $data['question_set_id'] = $questionSet->id;
        $this->questionService->createQuestion($data);

        return redirect()
            ->route('question-sets.show', $questionSet)
            ->with('success', 'Câu hỏi đã được thêm thành công.');
    }

    public function edit(QuestionSet $questionSet, Question $question): View
    {
        return view('questions.edit', compact('questionSet', 'question'));
    }

    public function update(UpdateQuestionRequest $request, QuestionSet $questionSet, Question $question): RedirectResponse
    {
        $this->questionService->updateQuestion($question->id, $request->validated());

        return redirect()
            ->route('question-sets.show', $questionSet)
            ->with('success', 'Câu hỏi đã được cập nhật thành công.');
    }

    public function destroy(QuestionSet $questionSet, Question $question): RedirectResponse
    {
        $this->questionService->delete($question->id);

        return redirect()
            ->route('question-sets.show', $questionSet)
            ->with('success', 'Câu hỏi đã được xóa thành công.');
    }
}
