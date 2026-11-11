<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($search = $request->search) {
            $query->where(fn($q) => $q->where('description', 'like', "%{$search}%")
                ->orWhere('module', 'like', "%{$search}%")
                ->orWhere('action', 'like', "%{$search}%")
                ->orWhere('ip_address', 'like', "%{$search}%")
                ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")));
        }

        if ($action = $request->action) $query->where('action', $action);
        if ($module = $request->module) $query->where('module', $module);
        if ($userId = $request->user_id) $query->where('user_id', $userId);
        if ($from = $request->from) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->to) $query->whereDate('created_at', '<=', $to);

        $summaryQuery = clone $query;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'today' => (clone $summaryQuery)->whereDate('created_at', today())->count(),
            'users' => (clone $summaryQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'critical' => (clone $summaryQuery)->whereIn('action', ['DELETE', 'delete', 'PASSWORD_RESET', 'FORCE_LOGOUT'])->count(),
        ];

        $activities = $query->latest()->paginate(20)->withQueryString();
        $loginHistories = LoginHistory::with('user')->latest('login_at')->limit(8)->get();
        $modules = ActivityLog::select('module', DB::raw('COUNT(*) as total'))
            ->whereNotNull('module')
            ->groupBy('module')
            ->orderBy('module')
            ->get();
        $actions = ActivityLog::whereNotNull('action')->distinct()->orderBy('action')->pluck('action');
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $actionBreakdown = (clone $summaryQuery)
            ->select('action', DB::raw('COUNT(*) as total'))
            ->groupBy('action')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return view('admin.activity-logs.index', compact(
            'activities',
            'loginHistories',
            'modules',
            'actions',
            'users',
            'summary',
            'actionBreakdown'
        ));
    }

    public function loginHistory(Request $request)
    {
        $query = LoginHistory::with('user');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('browser', 'like', "%{$search}%")
                    ->orWhere('device', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($userId = $request->user_id) $query->where('user_id', $userId);
        if ($status = $request->status) $query->where('status', $status);
        if ($device = $request->device) $query->where('device', $device);
        if ($from = $request->from) $query->whereDate('login_at', '>=', $from);
        if ($to = $request->to) $query->whereDate('login_at', '<=', $to);

        $summaryQuery = clone $query;
        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'success' => (clone $summaryQuery)->where('status', 'success')->count(),
            'failed' => (clone $summaryQuery)->where('status', 'failed')->count(),
            'locked' => (clone $summaryQuery)->where('status', 'locked')->count(),
        ];

        $histories = $query->latest('login_at')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $devices = LoginHistory::whereNotNull('device')->distinct()->orderBy('device')->pluck('device');

        return view('admin.activity-logs.login-history', compact('histories', 'summary', 'users', 'devices'));
    }
}
