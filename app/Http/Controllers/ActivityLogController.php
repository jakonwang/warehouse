<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isSuperAdmin()) {
                abort(403, '只有超级管理员可以查看操作日志');
            }
            return $next($request);
        });
    }

    /**
     * 显示操作日志列表
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user']);

        // 筛选条件
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        if ($request->filled('ip_address')) {
            $query->byIp($request->ip_address);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('abnormal') && $request->abnormal) {
            $query->abnormal();
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        $logs = $query->paginate(20);

        // 统计数据
        $stats = $this->getStats($request);

        // 筛选选项
        $users = User::select('id', 'real_name')->get();
        $actions = ActivityLog::distinct()->pluck('action');
        $ipAddresses = ActivityLog::distinct()->pluck('ip_address')->filter();

        return view('activity-logs.index', compact('logs', 'stats', 'users', 'actions', 'ipAddresses'));
    }

    /**
     * 显示操作日志详情
     */
    public function show(ActivityLog $activityLog)
    {
        return view('activity-logs.show', compact('activityLog'));
    }

    /**
     * 导出操作日志
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with(['user']);

        // 应用筛选条件
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV头部
            fputcsv($file, [
                'ID', '用户', '操作', '模型类型', '模型ID', 'IP地址', 
                '状态码', '执行时间', '响应大小', '创建时间'
            ]);

            // 数据行
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user->name ?? '未知用户',
                    $log->action_name,
                    $log->model_type_name,
                    $log->model_id,
                    $log->ip_address,
                    $log->status_code_text,
                    $log->execution_time_text,
                    $log->response_size_text,
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 清理旧日志
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        $deletedCount = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        return back()->with('success', "已清理 {$deletedCount} 条 {$days} 天前的日志记录");
    }

    /**
     * 获取统计数据
     */
    private function getStats(Request $request)
    {
        $query = ActivityLog::query();

        // 应用筛选条件
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $stats = [
            'total_logs' => $query->count(),
            'today_logs' => $query->whereDate('created_at', today())->count(),
            'abnormal_logs' => $query->abnormal()->count(),
            'unique_users' => $query->distinct('user_id')->count(),
            'unique_ips' => $query->distinct('ip_address')->count(),
            'avg_execution_time' => $query->avg('execution_time'),
            'avg_response_size' => $query->avg('response_size'),
        ];

        // 按操作类型统计
        $stats['action_stats'] = $query->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        // 按状态码统计
        $stats['status_stats'] = $query->select('status_code', DB::raw('count(*) as count'))
            ->groupBy('status_code')
            ->pluck('count', 'status_code')
            ->toArray();

        return $stats;
    }

    /**
     * 获取实时日志数据（用于AJAX）
     */
    public function getRecentLogs()
    {
        $logs = ActivityLog::with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($logs);
    }

    /**
     * 获取异常操作统计
     */
    public function getAbnormalStats()
    {
        $stats = [
            'high_execution_time' => ActivityLog::where('execution_time', '>', 5)->count(),
            'large_response_size' => ActivityLog::where('response_size', '>', 1024 * 1024)->count(),
            'error_status_codes' => ActivityLog::where('status_code', '>=', 400)->count(),
            'suspicious_ips' => ActivityLog::where('ip_address', 'like', '%192.168%')->count(),
        ];

        return response()->json($stats);
    }
}
