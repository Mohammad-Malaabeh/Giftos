<?php

namespace App\Http\Controllers;

use App\Models\PerformanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceDashboardController extends Controller
{
    public function index()
    {
        $this->authorize('viewPerformanceDashboard');
        
        // Get performance metrics
        $metrics = [
            'request_count' => PerformanceLog::count(),
            'avg_response_time' => round(PerformanceLog::avg('duration_ms'), 2) . 'ms',
            'error_rate' => PerformanceLog::where('status', '>=', 400)
                ->selectRaw('COUNT(*) * 100.0 / (SELECT COUNT(*) FROM performance_logs) as error_rate')
                ->value('error_rate') ?? 0,
            'slow_requests' => PerformanceLog::where('duration_ms', '>', 1000)->count(),
        ];

        // Get recent slow requests
        $slowRequests = PerformanceLog::with('user')
            ->where('duration_ms', '>', 1000)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get request statistics by endpoint
        $endpointStats = PerformanceLog::select('uri', 'method')
            ->selectRaw('COUNT(*) as request_count')
            ->selectRaw('AVG(duration_ms) as avg_duration')
            ->selectRaw('MAX(duration_ms) as max_duration')
            ->selectRaw('MIN(duration_ms) as min_duration')
            ->groupBy('uri', 'method')
            ->orderBy('avg_duration', 'desc')
            ->take(10)
            ->get();

        // Get error statistics
        $errorStats = PerformanceLog::where('status', '>=', 400)
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        // Get response time data for the last 24 hours
        $responseTimes = PerformanceLog::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('AVG(duration_ms) as avg_duration')
            )
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Format response time data for the chart
        $responseTimeData = array_fill(0, 24, 0);
        foreach ($responseTimes as $rt) {
            $responseTimeData[$rt->hour] = round($rt->avg_duration, 2);
        }

        return view('admin.performance.dashboard', compact(
            'metrics',
            'slowRequests',
            'endpointStats',
            'errorStats',
            'responseTimeData'
        ));
    }
}
