<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Jobs\ExportDataJob;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OrdersImport;
use Maatwebsite\Excel\Validators\ValidationException;

class OrderBulkController extends Controller
{
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'in:mark_paid,mark_shipped'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:orders,id'],
        ]);

        $orders = Order::whereIn('id', $data['ids'])->get();

        foreach ($orders as $order) {
            if ($data['action'] === 'mark_paid') {
                $order->payment_status = 'paid';
                if ($order->status === 'pending') $order->status = 'paid';
                if (!$order->paid_at) $order->paid_at = now();
            } elseif ($data['action'] === 'mark_shipped') {
                $order->status = 'shipped';
                if (!$order->shipped_at) $order->shipped_at = now();
            }
            $order->save();
            $order->recalcTotals();
        }

        return back()->with('success', 'Bulk action applied.');
    }

    public function export(Request $request)
    {
        return Excel::download(new OrdersExport, 'orders_export_' . now()->format('Y_m_d_His') . '.csv');
    }

    // New import method for orders
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,xlsx'],
        ]);

        try {
            Excel::import(new OrdersImport, $request->file('import_file'));
            return back()->with('success', 'Orders imported successfully!');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            
            // Group errors by type and count occurrences
            $errorGroups = [];
            $totalRows = 0;
            
            foreach ($failures as $failure) {
                $errorKey = implode(', ', $failure->errors());
                if (!isset($errorGroups[$errorKey])) {
                    $errorGroups[$errorKey] = [
                        'message' => $errorKey,
                        'rows' => [],
                        'count' => 0
                    ];
                }
                $errorGroups[$errorKey]['rows'][] = $failure->row();
                $errorGroups[$errorKey]['count']++;
                $totalRows++;
            }
            
            // Create summarized error messages
            $errors = [];
            $errors[] = "Import failed! Please check the following errors:";
            
            foreach ($errorGroups as $group) {
                if ($group['count'] === 1) {
                    $errors[] = "Row {$group['rows'][0]}: {$group['message']}";
                } else {
                    $rowList = implode(', ', array_slice($group['rows'], 0, 5));
                    if ($group['count'] > 5) {
                        $rowList .= " and " . ($group['count'] - 5) . " more";
                    }
                    $errors[] = "Rows {$rowList}: {$group['message']} ({$group['count']} rows affected)";
                }
            }
            
            $errors[] = "Total: {$totalRows} rows with errors out of " . count($failures) . " total failures.";
            
            return back()->withErrors($errors)->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }
}
