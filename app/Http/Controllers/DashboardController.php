<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $stats = $this->userService->getStatistics();
        $recentUsers = $this->userService->getRecentUsers(5);

        return view('dashboard', compact('stats', 'recentUsers'));
    }
}
