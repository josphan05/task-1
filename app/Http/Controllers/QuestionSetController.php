<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionSet\StoreQuestionSetRequest;
use App\Http\Requests\QuestionSet\UpdateQuestionSetRequest;
use App\Models\QuestionSet;
use App\Services\QuestionSetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionSetController extends Controller
{
    public function __construct(
        protected QuestionSetService $questionSetService
    ) {}

    public function index(): View
    {
        $questionSets = $this->questionSetService->getPaginatedWithQuestions(10);

        return view('question-sets.index', compact('questionSets'));
    }

    public function create(): View
    {
        return view('question-sets.create');
    }

    public function store(StoreQuestionSetRequest $request): RedirectResponse
    {
        $questionSet = $this->questionSetService->createQuestionSet($request->validated());

        return redirect()
            ->route('question-sets.show', $questionSet)
            ->with('success', "Bộ câu hỏi '{$questionSet->name}' đã được tạo thành công.");
    }

    public function show(QuestionSet $questionSet): View
    {
        $questionSet = $this->questionSetService->getWithOrderedQuestions($questionSet->id);
        return view('question-sets.show', compact('questionSet'));
    }

    public function edit(QuestionSet $questionSet): View
    {
        return view('question-sets.edit', compact('questionSet'));
    }

    public function update(UpdateQuestionSetRequest $request, QuestionSet $questionSet): RedirectResponse
    {
        $questionSet = $this->questionSetService->updateQuestionSet($questionSet->id, $request->validated());

        return redirect()
            ->back()
            ->with('success', "Bộ câu hỏi '{$questionSet->name}' đã được cập nhật thành công.");
    }

    public function destroy(QuestionSet $questionSet): RedirectResponse
    {
        $name = $questionSet->name;
        $this->questionSetService->delete($questionSet->id);

        return redirect()
            ->route('question-sets.index')
            ->with('success', "Bộ câu hỏi '{$name}' đã được xóa thành công.");
    }
}
