<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Attempt to authenticate a user.
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        return Auth::attempt($credentials, $remember);
    }

    /**
     * Log the user out.
     *
     * @return void
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return User|null
     */
    public function user(): ?User
    {
        return Auth::user();
    }

    /**
     * Check if user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return Auth::check();
    }

    /**
     * Get the ID of the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id(): int|string|null
    {
        return Auth::id();
    }

    /**
     * Validate user credentials without logging in.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials): bool
    {
        return Auth::validate($credentials);
    }

    /**
     * Log a user into the application.
     *
     * @param User $user
     * @param bool $remember
     * @return void
     */
    public function login(User $user, bool $remember = false): void
    {
        Auth::login($user, $remember);
    }

    /**
     * Regenerate the session.
     *
     * @return void
     */
    public function regenerateSession(): void
    {
        request()->session()->regenerate();
    }
}

