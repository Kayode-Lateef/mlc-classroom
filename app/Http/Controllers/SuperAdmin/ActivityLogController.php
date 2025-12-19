<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('action', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $logs = $query->paginate(50);

        // Statistics
        $stats = $this->getStatistics($request);

        // Get filter options
        $users = User::select('id', 'name', 'role')
            ->whereHas('activityLogs')
            ->orderBy('name')
            ->get();

        $modelTypes = ActivityLog::select('model_type')
            ->distinct()
            ->whereNotNull('model_type')
            ->orderBy('model_type')
            ->pluck('model_type');

        $actionTypes = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('superadmin.activity-logs.index', compact(
            'logs',
            'stats',
            'users',
            'modelTypes',
            'actionTypes'
        ));
    }

    /**
     * Display the specified activity log
     */
    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');

        // Get related logs (same model)
        $relatedLogs = [];
        if ($activityLog->model_type && $activityLog->model_id) {
            $relatedLogs = ActivityLog::with('user')
                ->where('model_type', $activityLog->model_type)
                ->where('model_id', $activityLog->model_id)
                ->where('id', '!=', $activityLog->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Get user's recent activity
        $userRecentActivity = [];
        if ($activityLog->user_id) {
            $userRecentActivity = ActivityLog::where('user_id', $activityLog->user_id)
                ->where('id', '!=', $activityLog->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // Parse user agent
        $userAgentInfo = $this->parseUserAgent($activityLog->user_agent);

        return view('superadmin.activity-logs.show', compact(
            'activityLog',
            'relatedLogs',
            'userRecentActivity',
            'userAgentInfo'
        ));
    }

    /**
     * Get statistics for activity logs
     */
    protected function getStatistics($request)
    {
        $query = ActivityLog::query();

        // Apply same filters as main query
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return [
            'total' => $query->count(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'this_week' => (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => (clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'unique_users' => (clone $query)->distinct('user_id')->count('user_id'),
            'actions_breakdown' => (clone $query)
                ->select('action', \DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('count', 'action')
                ->toArray(),
        ];
    }

    /**
     * Parse user agent string
     */
    protected function parseUserAgent($userAgent)
    {
        if (!$userAgent) {
            return null;
        }

        $info = [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'device' => 'Desktop',
        ];

        // Detect browser
        if (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $info['browser'] = 'Firefox ' . $matches[1];
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            $info['browser'] = 'Chrome ' . $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            if (!strpos($userAgent, 'Chrome')) {
                $info['browser'] = 'Safari ' . $matches[1];
            }
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            $info['browser'] = 'Edge ' . $matches[1];
        }

        // Detect platform
        if (preg_match('/Windows NT ([0-9.]+)/', $userAgent, $matches)) {
            $info['platform'] = 'Windows ' . $this->getWindowsVersion($matches[1]);
        } elseif (strpos($userAgent, 'Mac OS X') !== false) {
            $info['platform'] = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $info['platform'] = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $info['platform'] = 'Android';
            $info['device'] = 'Mobile';
        } elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false) {
            $info['platform'] = 'iOS';
            $info['device'] = 'Mobile';
        }

        return $info;
    }

    /**
     * Get Windows version name
     */
    protected function getWindowsVersion($version)
    {
        $versions = [
            '10.0' => '10/11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            '6.0' => 'Vista',
        ];

        return $versions[$version] ?? $version;
    }
}