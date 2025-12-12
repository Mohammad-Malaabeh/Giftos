<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();

        $today = now()->toDateString();

        // Today
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $revenueToday = (float) Order::whereDate('created_at', $today)
            ->where('payment_status', 'paid')
            ->sum('total');

        // Totals
        $ordersTotal = Order::count();
        $revenueTotal = (float) Order::where('payment_status', 'paid')->sum('total');

        // Range (optional filters)
        $rangeBase = Order::query()
            ->when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to));

        $ordersCount = (clone $rangeBase)->count();
        $revenue = (float) (clone $rangeBase)->where('payment_status', 'paid')->sum('total');

        // Top customers in range (paid orders only)
        $topCustomers = Order::selectRaw('user_id, COUNT(*) as orders, SUM(total) as spend')
            ->where('payment_status', 'paid')
            ->when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('spend')
            ->take(5)
            ->get();

        // Repeat customers (paid orders only)
        $repeatCustomers = Order::selectRaw('user_id, COUNT(*) as c')
            ->where('payment_status', 'paid')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->having('c', '>=', 2)
            ->count();

        // Top products (global, quick view)
        $topProducts = OrderItem::selectRaw('product_id, title, SUM(quantity) as qty')
            ->groupBy('product_id', 'title')
            ->orderByDesc('qty')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'ordersToday',
            'revenueToday',
            'ordersTotal',
            'revenueTotal',
            'from',
            'to',
            'ordersCount',
            'revenue',
            'topCustomers',
            'repeatCustomers',
            'topProducts'
        ));
    }
}
