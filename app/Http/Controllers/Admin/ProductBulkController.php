<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Jobs\ExportDataJob;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Imports\ProductsImport;

class ProductBulkController extends Controller
{
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'in:activate,deactivate'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        $status = $data['action'] === 'activate';
        Product::whereIn('id', $data['ids'])->update(['status' => $status]);

        return back()->with('success', 'Selected products updated.');
    }

    public function export(Request $request)
    {
        return Excel::download(new ProductsExport, 'products_export_' . now()->format('Y_m_d_His') . '.csv');
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,xlsx'],
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('import_file'));
            return back()->with('success', 'Products imported successfully!');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            // Handle validation failures, e.g., collect messages and show them
            $errors = collect($failures)->map(function ($failure) {
                return 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            })->toArray();
            return back()->withErrors($errors)->withInput(); // Use ->withErrors() to display them
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }
}
