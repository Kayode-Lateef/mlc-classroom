<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Models\SmsConfiguration;
use App\Models\User;
use Illuminate\Http\Request;

class SmsLogController extends Controller
{
    /**
     * Display SMS logs with filtering
     */
    public function index(Request $request)
    {
        $query = SmsLog::with('user');

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('sent_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sent_at', '<=', $request->date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Message type filter
        if ($request->filled('message_type')) {
            $query->where('message_type', $request->message_type);
        }

        // Provider filter
        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        // Recipient filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search by phone or message
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('phone_number', 'like', '%' . $request->search . '%')
                  ->orWhere('message_content', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'sent_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $logs = $query->paginate(20);

        // Get filter options
        $recipients = User::where('role', 'parent')->orderBy('name')->get();
        $providers = SmsLog::distinct('provider')->pluck('provider')->filter();

        // Statistics
        $stats = [
            'total_sms' => SmsLog::count(),
            'delivered' => SmsLog::where('status', 'delivered')->count(),
            'failed' => SmsLog::where('status', 'failed')->count(),
            'month_cost' => SmsLog::whereMonth('sent_at', now()->month)
                ->whereYear('sent_at', now()->year)
                ->sum('cost'),
        ];

        $activeProvider = SmsConfiguration::where('is_active', true)->value('provider');

        return view('superadmin.sms-logs.index', compact(
            'logs',
            'recipients',
            'providers',
            'stats',
            'activeProvider'
        ));
    }

    /**
     * Display single SMS log details
     */
    public function show(SmsLog $smsLog)
    {
        $smsLog->load('user');

        return view('superadmin.sms-logs.show', compact('smsLog'));
    }
}