<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\User; // Needed to map user names to IDs
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OrdersImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    private $users;

    public function __construct()
    {
        $this->users = User::all(['id', 'name'])->pluck('id', 'name');
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip rows where 'order_number' or 'user_name' is empty
        if (!isset($row['order_number']) || empty($row['order_number'])) {
            return null;
        }

        $userId = $this->users[$row['user_name']] ?? null;

        // Construct address arrays from flat CSV columns
        $shippingAddress = [
            'name'  => $row['shipping_name'] ?? null,
            'line1' => $row['shipping_address_1'] ?? null,
            'city'  => $row['shipping_city'] ?? null,
            'country' => $row['shipping_country'] ?? null,
            'zip'   => $row['shipping_zip'] ?? null,
        ];

        $billingAddress = [
            'name'  => $row['billing_name'] ?? null,
            'line1' => $row['billing_address_1'] ?? null,
            'city'  => $row['billing_city'] ?? null,
            'country' => $row['billing_country'] ?? null,
            'zip'   => $row['billing_zip'] ?? null,
        ];

        // You might want to update an existing order by 'order_number'
        // For simplicity, this example creates new orders.
        // $order = Order::where('number', $row['order_number'])->first();
        // if ($order) { $order->fill([...])->save(); return null; }

        return new Order([
            'user_id'           => $userId,
            'number'            => $row['order_number'],
            'status'            => $row['status'] ?? 'pending',
            'payment_status'    => $row['payment_status'] ?? 'unpaid',
            'subtotal'          => $row['subtotal'] ?? 0.00,
            'discount'          => $row['discount'] ?? 0.00,
            'shipping'          => $row['shipping'] ?? 0.00,
            'tax'               => $row['tax'] ?? 0.00,
            'total'             => $row['total'] ?? 0.00,
            'payment_method'    => $row['payment_method'] ?? 'unknown',
            'transaction_id'    => $row['transaction_id'] ?? null,
            'billing_address'   => $billingAddress,
            'shipping_address'  => $shippingAddress,
            'paid_at'           => isset($row['paid_at']) ? Carbon::parse($row['paid_at']) : null,
            'shipped_at'        => isset($row['shipped_at']) ? Carbon::parse($row['shipped_at']) : null,
            'completed_at'      => isset($row['completed_at']) ? Carbon::parse($row['completed_at']) : null,
            'created_at'        => isset($row['created_at']) ? Carbon::parse($row['created_at']) : null,
            // 'updated_at' is usually managed by Laravel automatically
        ]);
    }

    public function rules(): array
    {
        return [
            'order_number' => 'required|string|max:255|unique:orders,number',
            'user_name'    => 'required|string|exists:users,name',
            'status'       => ['required', Rule::in(['pending', 'processing', 'shipped', 'completed', 'cancelled'])],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'refunded', 'failed'])],
            'total'        => 'required|numeric|min:0',
            // Add rules for addresses and other fields as necessary
            'shipping_name' => 'nullable|string',
            'shipping_address_1' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_country' => 'nullable|string|size:2',
            'shipping_zip' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'user_name.exists' => 'The user ":input" does not exist.',
            'order_number.unique' => 'An order with number ":input" already exists.',
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}