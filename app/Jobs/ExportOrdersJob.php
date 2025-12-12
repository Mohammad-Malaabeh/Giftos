<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $exportType = 'orders';
        $fileName = $exportType . '_export_' . now()->format('Y_m_d_His') . '.csv';
        $filePath = 'exports/' . $fileName;

        Storage::makeDirectory('exports'); // Ensure the directory exists
        $fullPath = Storage::path($filePath); // Get the absolute path

        $handle = fopen($fullPath, 'w'); // Open file for writing

        // Define CSV Headers based on your Order model
        fputcsv($handle, [
            'ID', 'Order Number', 'User ID', 'User Name', 'Status', 'Payment Status',
            'Subtotal', 'Discount', 'Shipping', 'Tax', 'Total',
            'Payment Method', 'Transaction ID', 'Paid At', 'Shipped At', 'Completed At',
            'Shipping Name', 'Shipping Address 1', 'Shipping City', 'Shipping Country', 'Shipping ZIP',
            'Billing Name', 'Billing Address 1', 'Billing City', 'Billing Country', 'Billing ZIP',
            'Created At'
        ]);

        // Fetch orders in chunks and write to CSV
        Order::select(
                'id', 'number', 'user_id', 'status', 'payment_status',
                'subtotal', 'discount', 'shipping', 'tax', 'total',
                'payment_method', 'transaction_id', 'paid_at', 'shipped_at', 'completed_at',
                'shipping_address', 'billing_address', 'created_at' // Select address columns as arrays
            )
            ->with('user') // Eager load user for their name
            ->orderBy('id')
            ->chunk(1000, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    $shippingAddress = $order->shipping_address ?? [];
                    $billingAddress = $order->billing_address ?? [];

                    fputcsv($handle, [
                        $order->id,
                        $order->number,
                        $order->user_id,
                        $order->user->name ?? 'N/A',
                        $order->status,
                        $order->payment_status,
                        number_format($order->subtotal, 2),
                        number_format($order->discount, 2),
                        number_format($order->shipping, 2),
                        number_format($order->tax, 2),
                        number_format($order->total, 2),
                        $order->payment_method,
                        $order->transaction_id,
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
                    ]);
                }
            });

        fclose($handle); // Close the file handle

        // Notify the user who initiated the export
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ExportReadyNotification($fileName, $exportType));
        }
    }
}