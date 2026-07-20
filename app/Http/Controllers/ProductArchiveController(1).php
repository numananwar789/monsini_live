<?php

namespace App\Http\Controllers;

use App\Models\ProductArchive;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductArchiveController extends Controller
{
    public function index(Request $request)
    {

        
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }
        
        $archiveList = ProductArchive::distinct()->pluck('archive_name');
        $products = collect();

        if ($request->has('action')) {
            $archiveName = $request->action;
            $products = ProductArchive::where('archive_name', $archiveName)
                ->orderBy('product_style')
                ->orderBy('product_color')
                ->get()
                ->groupBy('product_style');
        }

        return view('product-archives.index', compact('archiveList', 'products'));
    }

    public function restore(Request $request)
    {
        $request->validate([
            'products' => 'sometimes|array',
            'products.*' => 'exists:dt_product_archive,product_style',
            'archive_name' => 'sometimes|string'
        ]);


        // dd($request->all());
        DB::beginTransaction();
        try {
            if ($request->has('products')) {
                // Restore selected products
                $productStyles = $request->products;

                // Insert into products table
                Product::insertUsing(
                    ['product_ID', 'product_style', 'product_color', 'product_size_range', 
                     'product_cost', 'product_wholesale_price', 'product_vendor_ID', 
                     'product_vendor_name', 'product_link', 'product_image', 'product_status' , 'sub_products'],
                    ProductArchive::whereIn('product_style', $productStyles)
                        ->select('product_ID', 'product_style', 'product_color', 'product_size_range', 
                                'product_cost', 'product_wholesale_price', 'product_vendor_ID', 
                                'product_vendor_name', 'product_link', 'product_image', 'product_status' , 'sub_products')
                );

                // Delete from archive
                ProductArchive::whereIn('product_style', $productStyles)->delete();
            } elseif ($request->has('archive_name')) {
                // Restore entire archive
                $archiveName = $request->archive_name;

                // Insert into products table
                Product::insertUsing(
                    ['product_ID', 'product_style', 'product_color', 'product_size_range', 
                     'product_cost', 'product_wholesale_price', 'product_vendor_ID', 
                     'product_vendor_name', 'product_link', 'product_image', 'product_status' , 'sub_products'],
                    ProductArchive::where('archive_name', $archiveName)
                        ->select('product_ID', 'product_style', 'product_color', 'product_size_range', 
                                'product_cost', 'product_wholesale_price', 'product_vendor_ID', 
                                'product_vendor_name', 'product_link', 'product_image', 'product_status' , 'sub_products')
                );

                // Delete from archive
                ProductArchive::where('archive_name', $archiveName)->delete();
            }

            DB::commit();
            return redirect()->route('product-archives.index')
                ->with('success', 'Products restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('product-archives.index')
                ->with('error', 'Error restoring products: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $productId)
    {
        $request->validate([
            'status' => 'required|boolean'
        ]);

        $product = ProductArchive::findOrFail($productId);
        $product->update(['product_status' => $request->status]);

        $message = $request->status 
            ? 'Product color activated successfully' 
            : 'Product color deactivated successfully';

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}