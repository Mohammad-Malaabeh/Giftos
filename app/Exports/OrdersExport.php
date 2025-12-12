<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\Queue\ShouldQueue; // <--- ADD THIS

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison, ShouldAutoSize, ShouldQueue // <--- ADD ShouldQueue
{
    public function query()
    {
        return Order::query()->with('user');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Order Number',
            'User Name',
            'Status',
            'Payment Status',
            'Subtotal',
            'Discount',
            'Shipping',
            'Tax',
            'Total',
            'Payment Method',
            'Transaction ID',
            'Paid At',
            'Shipped At',
            'Completed At',
            'Shipping Name',
            'Shipping Address 1',
            'Shipping City',
            'Shipping Country',
            'Shipping ZIP',
            'Billing Name',
            'Billing Address 1',
            'Billing City',
            'Billing Country',
            'Billing ZIP',
            'Created At',
            'Updated At',
        ];
    }

    public function map($order): array
    {
        $shippingAddress = $order->shipping_address ?? [];
        $billingAddress = $order->billing_address ?? [];

        return [
            $order->id,
            $order->number,
            $order->user->name ?? 'N/A',
            $order->status,
            $order->payment_status,
            number_format($order->subtotal, 2),
            number_format($order->discount, 2),
            number_format($order->shipping, 2),
            number_format($order->tax, 2),
            number_format($order->total, 2),
            $order->payment_method,
            $order->transaction_id ?? '',
            $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '',
            $order->shipped_at ? $order->shipped_at->format('Y-m-d H:i:s') : '',
            $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : '',
            $shippingAddress['name'] ?? '',
            $shippingAddress['line1'] ?? '',
            $shippingAddress['city'] ?? '',
            $shippingAddress['country'] ?? '',
            $shippingAddress['zip'] ?? '',
            $billingAddress['name'] ?? '',
            $billingAddress['line1'] ?? '',
            $billingAddress['city'] ?? '',
            $billingAddress['country'] ?? '',
            $billingAddress['zip'] ?? '',
            $order->created_at->format('Y-m-d H:i:s'),
            $order->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
