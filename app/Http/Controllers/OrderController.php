<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $user = $request->user();

        // If user is admin or manager, show all orders, otherwise only user's orders
        $query = $user->isAdmin() || $user->isManager()
            ? Order::query()
            : Order::where('user_id', $user->id);

        $orders = $query->with(['items.product', 'user'])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        // Eager load relationships for the view
        $order->load(['items.product', 'user']);

        return view('orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorize('cancel', $order);

        // Add logic to cancel the order
        $order->update(['status' => 'canceled']);

        // Log the cancellation
        activity()
            ->causedBy($request->user())
            ->performedOn($order)
            ->log('cancelled order');

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order has been cancelled successfully.');
    }

    public function refund(Request $request, Order $order)
    {
        $this->authorize('refund', $order);

        // Add logic to process refund
        // This would typically integrate with a payment gateway

        $order->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        // Log the refund
        activity()
            ->causedBy($request->user())
            ->performedOn($order)
            ->withProperties(['amount' => $order->total])
            ->log('processed refund');

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order has been refunded successfully.');
    }
}
