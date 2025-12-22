<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionSetCommand\StoreQuestionSetCommandRequest;
use App\Http\Requests\QuestionSetCommand\UpdateQuestionSetCommandRequest;
use App\Models\QuestionSetCommand;
use App\Services\QuestionSetCommandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionSetCommandController extends Controller
{
    public function __construct(
        protected QuestionSetCommandService $commandService
    ) {}

    public function index(): View
    {
        $commands = $this->commandService->getPaginatedWithQuestionSet(10);

        return view('question-set-commands.index', compact('commands'));
    }

    public function create(): View
    {
        $questionSets = $this->commandService->getActiveQuestionSets();
        return view('question-set-commands.create', compact('questionSets'));
    }

    public function store(StoreQuestionSetCommandRequest $request): RedirectResponse
    {
        $command = $this->commandService->createCommand($request->validated());

        return redirect()
            ->route('question-set-commands.index')
            ->with('success', "Command '{$command->command}' đã được tạo thành công.");
    }

    public function edit(QuestionSetCommand $questionSetCommand): View
    {
        $questionSets = $this->commandService->getActiveQuestionSets();
        return view('question-set-commands.edit', compact('questionSetCommand', 'questionSets'));
    }

    public function update(UpdateQuestionSetCommandRequest $request, QuestionSetCommand $questionSetCommand): RedirectResponse
    {
        $command = $this->commandService->updateCommand($questionSetCommand->id, $request->validated());

        return redirect()
            ->back()
            ->with('success', "Command '{$command->command}' đã được cập nhật thành công.");
    }

    public function destroy(QuestionSetCommand $questionSetCommand): RedirectResponse
    {
        $command = $questionSetCommand->command;
        $this->commandService->delete($questionSetCommand->id);

        return redirect()
            ->route('question-set-commands.index')
            ->with('success', "Command '{$command}' đã được xóa thành công.");
    }
}
