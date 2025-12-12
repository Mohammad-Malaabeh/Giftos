@extends('layouts.admin')

@push('styles')
<style>
    .stat-card {
        border-radius: 8px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-card .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 10px 0;
    }
    .stat-card .stat-label {
        font-size: 1rem;
        opacity: 0.9;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Performance Dashboard</h1>
        <div>
            <span class="text-muted">Last updated: {{ now()->format('M j, Y g:i A') }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                <div class="stat-label">Total Requests</div>
                <div class="stat-value">{{ number_format($metrics['request_count']) }}</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i> 12% from last week
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(45deg, #1cc88a, #13855c);">
                <div class="stat-label">Avg. Response Time</div>
                <div class="stat-value">{{ $metrics['avg_response_time'] }}</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-down"></i> 5% faster than last week
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(45deg, #f6c23e, #dda20a);">
                <div class="stat-label">Error Rate</div>
                <div class="stat-value">{{ number_format($metrics['error_rate'], 2) }}%</div>
                <div class="stat-trend">
                    <i class="fas {{ $metrics['error_rate'] > 1 ? 'fa-arrow-up text-danger' : 'fa-arrow-down text-success' }}"></i>
                    {{ $metrics['error_rate'] > 1 ? 'Higher' : 'Lower' }} than threshold
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(45deg, #e74a3b, #be2617);">
                <div class="stat-label">Slow Requests</div>
                <div class="stat-value">{{ number_format($metrics['slow_requests']) }}</div>
                <div class="stat-trend">
                    <i class="fas {{ $metrics['slow_requests'] > 50 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                    {{ $metrics['slow_requests'] > 50 ? 'Higher' : 'Lower' }} than usual
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Response Times (Last 24 Hours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="responseTimeChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Error Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="errorChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Slow Requests Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Slow Requests</h5>
            <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Method</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>User</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slowRequests as $request)
                        <tr>
                            <td><span class="badge bg-primary">{{ $request->method }}</span></td>
                            <td class="text-truncate" style="max-width: 200px;" title="{{ $request->uri }}">
                                {{ $request->uri }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $request->status >= 400 ? 'danger' : 'success' }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td>{{ number_format($request->duration_ms) }}ms</td>
                            <td>{{ $request->user ? $request->user->name : 'Guest' }}</td>
                            <td>{{ $request->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No slow requests found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Endpoint Performance Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Endpoint Performance</h5>
            <a href="#" class="btn btn-sm btn-outline-primary">Export Data</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>Requests</th>
                            <th>Avg. Duration</th>
                            <th>Min</th>
                            <th>Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($endpointStats as $stat)
                        <tr>
                            <td><span class="badge bg-primary">{{ $stat->method }}</span></td>
                            <td class="text-truncate" style="max-width: 200px;" title="{{ $stat->uri }}">
                                {{ $stat->uri }}
                            </td>
                            <td>{{ number_format($stat->request_count) }}</td>
                            <td>{{ number_format($stat->avg_duration, 2) }}ms</td>
                            <td>{{ number_format($stat->min_duration, 2) }}ms</td>
                            <td>{{ number_format($stat->max_duration, 2) }}ms</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No endpoint data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Response Time Chart
    new Chart(document.getElementById('responseTimeChart'), {
        type: 'line',
        data: {
            labels: Array.from({length: 24}, (_, i) => `${i}:00`),
            datasets: [{
                label: 'Avg. Response Time (ms)',
                data: @json($responseTimeData),
                borderColor: 'rgba(78, 115, 223, 1)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Response Time (ms)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Hour of Day'
                    }
                }
            }
        }
    });

    // Error Distribution Chart
    new Chart(document.getElementById('errorChart'), {
        type: 'doughnut',
        data: {
            labels: ['2xx Success', '3xx Redirects', '4xx Client Errors', '5xx Server Errors'],
            datasets: [{
                data: [75, 10, 10, 5], // Replace with actual data
                backgroundColor: [
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b'
                ],
                hoverBackgroundColor: [
                    '#17a673',
                    '#2c9faf',
                    '#dda20a',
                    '#be2617'
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '70%',
        }
    });
</script>
@endpush
