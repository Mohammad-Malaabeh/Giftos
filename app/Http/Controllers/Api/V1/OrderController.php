<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display user's orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
    }

    /**
     * Store a new order
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $validator = validator($request->all(), [
            'shipping_address_id' => ['required', 'exists:user_addresses,id'],
            'payment_method' => ['required', 'string', 'in:stripe,square,cod'],
            'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => "Product '{$item->product->title}' has insufficient stock"
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = $cartItems->sum(function ($item) {
                return $item->quantity * ($item->product->sale_price ?? $item->product->price);
            });

            $shipping = 10.00; // Fixed shipping cost
            $discount = 0;

            // Apply coupon if provided
            if ($request->coupon_code) {
                $coupon = \App\Models\Coupon::where('code', $request->coupon_code)
                    ->where('active', true)
                    ->first();

                if ($coupon && $coupon->isValid($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                }
            }

            $tax = ($subtotal - $discount) * 0.08; // 8% tax
            $total = $subtotal + $shipping + $tax - $discount;

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'shipping_address_id' => $request->shipping_address_id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->sale_price ?? $item->product->price,
                ]);

                // Update product stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            $user->cartItems()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order->load(['items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'user']);

        return response()->json([
            'data' => $order
        ]);
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        if (!in_array($order->status, ['pending', 'paid'])) {
            return response()->json(['message' => 'Order cannot be canceled'], 422);
        }

        try {
            DB::beginTransaction();

            // Restore product stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $order->update(['status' => 'canceled']);

            DB::commit();

            return response()->json([
                'message' => 'Order canceled successfully',
                'data' => $order->fresh()->load(['items.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order status
     */
    public function status(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json([
            'data' => [
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]
        ]);
    }

    /**
     * Update order status (admin only)
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validator = validator($request->all(), [
            'status' => ['required', 'string', 'in:pending,paid,shipped,completed,canceled,refunded'],
            'tracking_number' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update($request->only(['status', 'tracking_number', 'notes']));

        return response()->json([
            'message' => 'Order status updated',
            'data' => $order->load(['user', 'items.product'])
        ]);
    }

    /**
     * Get order statistics (admin only)
     */
    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class); // Or 'viewStats'

        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $stats = [
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Order::whereBetween('created_at', [$startDate, $endDate])->sum('total'),
            'average_order_value' => Order::whereBetween('created_at', [$startDate, $endDate])->avg('total'),
            'orders_by_status' => [
                'pending' => Order::where('status', 'pending')->count(),
                'paid' => Order::where('status', 'paid')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'canceled' => Order::where('status', 'canceled')->count(),
            ]
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Export orders (admin only)
     */
    public function export(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $startDate = $request->get('start_date');
        $endDate = $request->get('endDate');
        $format = $request->get('format', 'csv');

        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = $query->with(['user', 'items.product'])->get();

        // Implementation would depend on your export library
        // This is a placeholder for the export functionality

        return response()->json([
            'message' => 'Export functionality to be implemented',
            'data' => [
                'orders_count' => $orders->count(),
                'format' => $format,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ]
            ]
        ]);
    }
}
