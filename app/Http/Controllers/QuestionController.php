<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function create(QuestionSet $questionSet): View
    {
        return view('questions.create', compact('questionSet'));
    }

    public function store(Request $request, QuestionSet $questionSet): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'field_name' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
            'validation_rule' => 'nullable|string',
            'error_message' => 'nullable|string',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        // Parse options từ text format "Text|Value" thành array
        if (!empty($validated['options'])) {
            $options = [];
            $lines = explode("\n", $validated['options']);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $parts = explode('|', $line, 2);
                $text = trim($parts[0]);
                $value = isset($parts[1]) ? trim($parts[1]) : $text;

                if (!empty($text)) {
                    $options[] = [
                        'text' => $text,
                        'value' => $value,
                    ];
                }
            }
            $validated['options'] = !empty($options) ? $options : null;
        } else {
            $validated['options'] = null;
        }

        $validated['question_set_id'] = $questionSet->id;
        Question::create($validated);

        return redirect()
            ->route('question-sets.show', $questionSet)
            ->with('success', 'Câu hỏi đã được thêm thành công.');
    }

    public function edit(QuestionSet $questionSet, Question $question): View
    {
        return view('questions.edit', compact('questionSet', 'question'));
    }

    public function update(Request $request, QuestionSet $questionSet, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'field_name' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
            'validation_rule' => 'nullable|string',
            'error_message' => 'nullable|string',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        // Parse options từ text format "Text|Value" thành array
        if (!empty($validated['options'])) {
            $options = [];
            $lines = explode("\n", $validated['options']);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $parts = explode('|', $line, 2);
                $text = trim($parts[0]);
                $value = isset($parts[1]) ? trim($parts[1]) : $text;

                if (!empty($text)) {
                    $options[] = [
                        'text' => $text,
                        'value' => $value,
                    ];
                }
            }
            $validated['options'] = !empty($options) ? $options : null;
        } else {
            $validated['options'] = null;
        }

        $question->update($validated);

        return redirect()
            ->route('question-sets.show', $questionSet)
            ->with('success', 'Câu hỏi đã được cập nhật thành công.');
    }

    public function destroy(QuestionSet $questionSet, Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()
            ->route('question-sets.show', $questionSet)
            ->with('success', 'Câu hỏi đã được xóa thành công.');
    }
}
