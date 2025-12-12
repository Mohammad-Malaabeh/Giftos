<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportProductsJob implements ShouldQueue
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
        $exportType = 'products';
        $fileName = $exportType . '_export_' . now()->format('Y_m_d_His') . '.csv';
        $filePath = 'exports/' . $fileName;

        Storage::makeDirectory('exports'); // Ensure the directory exists
        $fullPath = Storage::path($filePath); // Get the absolute path

        $handle = fopen($fullPath, 'w'); // Open file for writing

        // Define CSV Headers based on your Product model
        fputcsv($handle, [
            'ID', 'Title', 'SKU', 'Price', 'Sale Price', 'Stock',
            'Status', 'Category', 'Created At', 'Updated At'
        ]);

        // Fetch products in chunks and write to CSV
        Product::select(
                'id', 'title', 'sku', 'price', 'sale_price', 'stock',
                'status', 'category_id', 'created_at', 'updated_at'
            )
            ->with('category') // Eager load category for its name
            ->orderBy('id')
            ->chunk(1000, function ($products) use ($handle) {
                foreach ($products as $product) {
                    fputcsv($handle, [
                        $product->id,
                        $product->title,
                        $product->sku,
                        number_format($product->price, 2),
                        number_format($product->sale_price, 2),
                        $product->stock,
                        $product->status ? 'Active' : 'Inactive',
                        $product->category->name ?? 'N/A', // Get category name or 'N/A'
                        $product->created_at->format('Y-m-d H:i:s'),
                        $product->updated_at->format('Y-m-d H:i:s'),
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