<?php

namespace App\Http\Controllers;

use App\Models\QuestionSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionSetController extends Controller
{
    public function index(): View
    {
        $questionSets = QuestionSet::with('questions')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('question-sets.index', compact('questionSets'));
    }

    public function create(): View
    {
        return view('question-sets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_message' => 'nullable|string',
            'completion_message' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // Nếu set làm mặc định, bỏ mặc định của các bộ khác
        if ($validated['is_default'] ?? false) {
            QuestionSet::where('is_default', true)->update(['is_default' => false]);
        }

        $questionSet = QuestionSet::create($validated);

        return redirect()
            ->route('question-sets.show', $questionSet)
            ->with('success', "Bộ câu hỏi '{$questionSet->name}' đã được tạo thành công.");
    }

    public function show(QuestionSet $questionSet): View
    {
        $questionSet->load(['questions' => function($query) {
            $query->orderBy('order');
        }]);
        return view('question-sets.show', compact('questionSet'));
    }

    public function edit(QuestionSet $questionSet): View
    {
        return view('question-sets.edit', compact('questionSet'));
    }

    public function update(Request $request, QuestionSet $questionSet): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_message' => 'nullable|string',
            'completion_message' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // Nếu set làm mặc định, bỏ mặc định của các bộ khác
        if ($validated['is_default'] ?? false) {
            QuestionSet::where('id', '!=', $questionSet->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $questionSet->update($validated);

        return redirect()
            ->back()
            ->with('success', "Bộ câu hỏi '{$questionSet->name}' đã được cập nhật thành công.");
    }

    public function destroy(QuestionSet $questionSet): RedirectResponse
    {
        $name = $questionSet->name;
        $questionSet->delete();

        return redirect()
            ->route('question-sets.index')
            ->with('success', "Bộ câu hỏi '{$name}' đã được xóa thành công.");
    }
}
