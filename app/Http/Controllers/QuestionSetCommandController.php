<?php

namespace App\Http\Controllers;

use App\Models\QuestionSet;
use App\Models\QuestionSetCommand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionSetCommandController extends Controller
{
    public function index(): View
    {
        $commands = QuestionSetCommand::with('questionSet')
            ->orderBy('command')
            ->paginate(10);

        return view('question-set-commands.index', compact('commands'));
    }

    public function create(): View
    {
        $questionSets = QuestionSet::where('is_active', true)->get();
        return view('question-set-commands.create', compact('questionSets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'command' => 'required|string|max:255|unique:question_set_commands,command',
            'question_set_id' => 'required|exists:question_sets,id',
            'response_message' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
            
        if (!str_starts_with($validated['command'], '/')) {
            $validated['command'] = '/' . $validated['command'];
        }

        QuestionSetCommand::create($validated);

        return redirect()
            ->route('question-set-commands.index')
            ->with('success', "Command '{$validated['command']}' đã được tạo thành công.");
    }

    public function edit(QuestionSetCommand $questionSetCommand): View
    {
        $questionSets = QuestionSet::where('is_active', true)->get();
        return view('question-set-commands.edit', compact('questionSetCommand', 'questionSets'));
    }

    public function update(Request $request, QuestionSetCommand $questionSetCommand): RedirectResponse
    {
        $validated = $request->validate([
            'command' => 'required|string|max:255|unique:question_set_commands,command,' . $questionSetCommand->id,
            'question_set_id' => 'required|exists:question_sets,id',
            'response_message' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!str_starts_with($validated['command'], '/')) {
            $validated['command'] = '/' . $validated['command'];
        }

        $questionSetCommand->update($validated);

        return redirect()
            ->back()
            ->with('success', "Command '{$validated['command']}' đã được cập nhật thành công.");
    }

    public function destroy(QuestionSetCommand $questionSetCommand): RedirectResponse
    {
        $command = $questionSetCommand->command;
        $questionSetCommand->delete();

        return redirect()
            ->route('question-set-commands.index')
            ->with('success', "Command '{$command}' đã được xóa thành công.");
    }
}
