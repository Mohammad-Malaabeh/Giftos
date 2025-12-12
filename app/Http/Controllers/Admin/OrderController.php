<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Mail\OrderStatusUpdatedMail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $paymentStatus = $request->string('payment_status')->toString();
        $from = $request->date('from');
        $to = $request->date('to');

        $orders = Order::query()
            ->with('user')
            ->when($q, function ($qq) use ($q) {
                $qq->where('number', 'like', "%{$q}%")
                    ->orWhere('transaction_id', 'like', "%{$q}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('email', 'like', "%{$q}%"));
            })
            ->when($status !== '', fn($qq) => $qq->where('status', $status))
            ->when($paymentStatus !== '', fn($qq) => $qq->where('payment_status', $paymentStatus))
            ->when($from, fn($qq) => $qq->whereDate('created_at', '>=', $from))
            ->when($to, fn($qq) => $qq->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'q', 'status', 'paymentStatus', 'from', 'to'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,processing,paid,shipped,delivered,completed,cancelled,refunded'],
            'payment_status' => ['nullable', 'in:unpaid,paid,refunded,failed'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'carrier' => ['nullable', 'string', 'max:50'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);


        if ($order->status === 'shipped' && !$order->shipped_at)
            $order->shipped_at = now();
        if ($order->status === 'completed' && !$order->completed_at)
            $order->completed_at = now();

        $order->fill($validated);

        // ensure timestamps based on payment_status first
        if ($order->payment_status === 'paid' && !$order->paid_at) {
            $order->paid_at = now();
            // optionally normalize order status if it's pending
            if ($order->status === 'pending') {
                $order->status = 'paid';
            }
        }
        if ($order->status === 'shipped' && !$order->shipped_at)
            $order->shipped_at = now();
        if ($order->status === 'completed' && !$order->completed_at)
            $order->completed_at = now();

        // Save attribute changes before recalculating totals
        $order->save();
        Activity::log('order.updated', $order, [
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'tracking' => $order->tracking_number,
        ]);

        $order->recalcTotals();

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated.');
    }


}
