<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $users = $this->userService->getPaginatedUsers([
            'search' => $request->get('search'),
            'status' => $request->get('status'),
        ], 5);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->userService->createUser($request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', "Người dùng {$user->name} đã được tạo thành công.");
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {

        $this->userService->updateUser($user->id, $request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', "Người dùng {$user->name} đã được cập nhật thành công.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $name = $user->name;
        $this->userService->deleteUser($user->id);

        return redirect()
            ->route('users.index')
            ->with('success', "Người dùng {$name} đã được xóa thành công.");
    }
}
