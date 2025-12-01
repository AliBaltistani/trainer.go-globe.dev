<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin Dashboard API Controller
 * 
 * Handles dashboard statistics and activity data
 * 
 * @package     Laravel CMS App
 * @subpackage  Controllers\Api
 * @category    Dashboard
 * @author      Go Globe CMS Team
 * @since       1.0.0
 */
class AdminDashboardController extends ApiBaseController
{
    /**
     * Get dashboard statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // 1. Total Users
            $totalUsers = User::count();
            
            // 2. Active Users (Users with verified email as proxy for active status)
            // Also considering users who have logged in recently if sessions were tracked, 
            // but strictly per requirements and available fields, we'll use verified users.
            // Alternatively, we can count users created in the last 30 days as "New Active Users" 
            // but "Active Users" usually implies current engagement. 
            // Given the constraints, we'll return verified users count.
            $activeUsers = User::whereNotNull('email_verified_at')->count();
            
            // 3. User Activity Graph (Monthly registrations for last 6-12 months)
            // This matches the graph shown in the image "Monthly Active Users" (or growth)
            $userActivity = User::select(
                DB::raw('COUNT(*) as count'), 
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw("DATE_FORMAT(created_at, '%M') as month_name")
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month', 'month_name')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month_name,
                    'count' => $item->count
                ];
            });

            // Fill in missing months with 0
            $activityData = [];
            $currentDate = now()->subMonths(11);
            for ($i = 0; $i < 12; $i++) {
                $monthKey = $currentDate->format('F'); // Full month name
                $found = $userActivity->firstWhere('month', $monthKey);
                $activityData[] = [
                    'month' => $monthKey,
                    'count' => $found ? $found['count'] : 0
                ];
                $currentDate->addMonth();
            }

            $data = [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'user_activity' => $activityData
            ];

            return $this->sendResponse($data, 'Dashboard data retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Dashboard API Error: ' . $e->getMessage());
            return $this->sendError('Failed to retrieve dashboard data', ['error' => $e->getMessage()], 500);
        }
    }
}
