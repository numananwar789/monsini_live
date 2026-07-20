<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductArchive;
use App\Models\Vendor;
// use App\Imports\ProductsImport;
// use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Imports\ProductsImport;
use Shuchkin\SimpleXLSXGen;

class ProductController extends Controller
{


    public function index()
    {


        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        // $products = Product::select(
        //     'product_ID',
        //     'product_image',
        //     'product_style',
        //     'product_color',
        //     'product_size_range',
        //     'product_cost',
        //     'product_wholesale_price',
        //     'product_vendor_name',
        //     'product_link'
        // )
        //     ->groupBy('product_style')
        //     ->orderBy('product_ID', 'DESC')
        //     ->get();

        $products = Product::selectRaw('
                MAX(product_ID) as product_ID,
                MAX(product_image) as product_image,
                MAX(factory_style) as factory_style,
                product_style,
                MAX(product_color) as product_color,
                MAX(product_size_range) as product_size_range,
                MAX(product_cost) as product_cost,
                MAX(product_wholesale_price) as product_wholesale_price,
                MAX(product_vendor_name) as product_vendor_name,
                MAX(sub_products) as sub_products,
                MAX(product_link) as product_link,
                MAX(dt_product.show_from_inventory) as inventory_override
            ')
            ->groupBy('product_style')
            ->orderBy('product_style', 'ASC')
            ->get();
        $allSubProducts = \App\Models\SubProduct::pluck('sub_product_name')->toArray();
        return view('products.index', compact('products', 'allSubProducts'));
    }

    public function action(Request $request)
    {
        // dd($request->all());
        $action = $request->input('action');
        $styleNumber = rtrim($request->input('styleNumber'), ' ');
        $id = $request->input('id');

        if ($styleNumber) {
            switch ($action) {
                case 'Delete':
                    Product::where('product_style', $styleNumber)->delete();
                    break;
                case 'Active':
                    Product::where('product_style', $styleNumber)
                        ->update(['product_status' => 0]);
                    break;
                case 'Inactive':
                    Product::where('product_style', $styleNumber)
                        ->update(['product_status' => 1]);
                    break;
            }
            return redirect()->route('products.index');
        } else {
            switch ($action) {
                case 'Delete':
                    Product::where('product_style', $styleNumber)->delete();
                    break;
                case 'Active':
                    Product::where('product_ID', $id)
                        ->update(['product_status' => 1]);
                    break;
                case 'Inactive':
                    Product::where('product_ID', $id)
                        ->update(['product_status' => 0]);
                    break;
            }
            return response()->json(['success' => true]);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));
            return redirect()->route('products.index')
                ->with('success', 'Products imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing products: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $vendors = Vendor::all();
        $allSubProducts = \App\Models\SubProduct::pluck('sub_product_name')->toArray();
        return view('products.create', compact('vendors', 'allSubProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'style' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'wholesale' => 'required|numeric|min:0',
            'vendor_id' => 'required|exists:dt_vendor,vendor_ID',
            'link' => 'nullable|string',
            'image' => 'nullable|string',
            'factory_style' => 'nullable|string',
            'sub_products' => 'nullable|array',
            'sub_products.*' => 'string',
        ]);

        // Check if product already exists
        $existingProduct = Product::where('product_style', strtolower($validated['style']))
            ->where('product_color', strtolower($validated['color']))
            ->first();

        if ($existingProduct) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Product with this style and color already exists!');
        }

        // Get vendor details
        $vendor = Vendor::findOrFail($validated['vendor_id']);

        // Create new product
        Product::create([
            'product_style' => strtolower($validated['style']),
            'factory_style' => strtolower($validated['factory_style']),
            'product_color' => strtolower($validated['color']),
            'product_size_range' => $validated['size'],
            'product_cost' => $validated['cost'],
            'product_wholesale_price' => $validated['wholesale'],
            'product_vendor_ID' => $validated['vendor_id'],
            'product_vendor_name' => $vendor->vendor_comp_name,
            'product_link' => $validated['link'] ?? "",
            'product_image' => $validated['image'] ?? "",
            'sub_products' => $validated['sub_products'] ?? [],
            'product_status' => 1
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product added successfully!');
    }


    public function archive(Request $request)
    {
        $validated = $request->validate([
            'selectedItems' => 'required|array',
            'selectedItems.*' => 'exists:dt_product,product_style',
            'archiveName' => 'required|string|max:255'
        ]);

        \DB::transaction(function () use ($validated) {
            // Get products to archive
            $products = Product::whereIn('product_style', $validated['selectedItems'])->get();

            // Create archive records
            foreach ($products as $product) {
                ProductArchive::create([
                    'product_ID' => $product->product_ID,
                    'product_style' => $product->product_style,
                    'factory_style' => $product->factory_style,
                    'product_color' => $product->product_color,
                    'product_size_range' => $product->product_size_range,
                    'product_cost' => $product->product_cost,
                    'product_wholesale_price' => $product->product_wholesale_price,
                    'product_vendor_ID' => $product->product_vendor_ID,
                    'product_vendor_name' => $product->product_vendor_name,
                    'product_link' => $product->product_link,
                    'product_image' => $product->product_image,
                    'sub_products' => $product->sub_products,
                    'product_status' => 1, // Archived products are inactive
                    'archive_name' => $validated['archiveName']
                ]);
            }

            // Delete original products
            Product::whereIn('product_style', $validated['selectedItems'])->delete();
        });

        return response()->json(['success' => true]);
    }


    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $vendors = Vendor::all();
        $allSubProducts = \App\Models\SubProduct::pluck('sub_product_name')->toArray();
        // Get all colors for this product style
        $colors = Product::where('product_style', $product->product_style)
            ->select('product_ID', 'product_color')
            ->get();
        return view('products.edit', compact('product', 'vendors', 'colors', 'allSubProducts'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'style' => 'required|string|max:255',
            'size' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'wholesale' => 'required|numeric|min:0',
            'vendor_id' => 'required|exists:dt_vendor,vendor_ID',
            'link' => 'nullable|string',
            'image' => 'nullable|string',
            'factory_style' => 'nullable|string',
            'sub_products' => 'nullable|array',
            'sub_products.*' => 'string',
        ]);

        // dd();
        $product = Product::findOrFail($id);
        $vendor = Vendor::findOrFail($validated['vendor_id']);

        // Update all products with the same style (main product and color variants)
        Product::where('product_style', $product->product_style)
            ->update([
                'product_style' => $validated['style'],
                'factory_style' => $validated['factory_style'],
                'product_size_range' => $validated['size'],
                'product_cost' => $validated['cost'],
                'product_wholesale_price' => $validated['wholesale'],
                'product_vendor_ID' => $validated['vendor_id'],
                'product_vendor_name' => $vendor->vendor_comp_name,
                'product_link' => $validated['link'] ?? "",
                'sub_products' => $validated['sub_products'] ?? [],
                'product_image' => $validated['image'] ?? ""
            ]);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function updateColor(Request $request)
    {
        $validated = $request->validate([
            'color_id' => 'required|exists:dt_product,product_ID',
            'color_name' => 'required|string|max:255'
        ]);

        $product = Product::findOrFail($validated['color_id']);
        $product->update([
            'product_color' => trim($validated['color_name'])
        ]);

        return response()->json(['success' => true]);
    }

    public function download()
    {
        // Get all products
        $products = Product::all();

        // Prepare headers
        $headers = [
            "Product ID",
            "Style",
            "Factory Style",
            "Color",
            "Size Range",
            "Cost",
            "Wholesale Price",
            "Sub Products",
            "Vendor ID",
            "Vendor Name",
            "Link",
            "Image"
        ];

        // Prepare data array
        $data = [$headers];

        // Add product data
        foreach ($products as $product) {
            $data[] = [
                $product->product_ID,
                strtoupper($product->product_style),
                $product->factory_style,
                strtoupper($product->product_color),
                $product->product_size_range,
                $product->product_cost,
                $product->product_wholesale_price,
                implode(', ', $product->sub_products ?? []),
                $product->product_vendor_ID,
                $product->product_vendor_name,
                $product->product_link,
                $product->product_image
            ];
        }

        // Generate filename with current date
        $filename = 'products_bkp_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        // Generate and download XLSX
        return SimpleXLSXGen::fromArray($data)
            ->downloadAs($filename);
    }

    public function getWholesalePrice(Request $request)
    {
        $style = $request->input('style');

        $price = Product::where('product_style', $style)
            ->value('product_wholesale_price'); // fetch only the column

        return response()->json($price); // return as JSON
    }
    // public function index()
    // {
    //     // $products = Product::with(['variants', 'vendor'])
    //     //             ->orderBy('style')
    //     //             ->get();
    //     $products = [];
    //     return view('products.index',compact('products'));
    // }

    // public function create()
    // {
    //     $vendors = Vendor::all();
    //     return view('admin.products.create', compact('vendors'));
    // }

    // public function store(Request $request)
    // {
    //     // Validation
    //     $validated = $request->validate([
    //         'style' => 'required|unique:products',
    //         'image' => 'required|url',
    //         'link' => 'required|url',
    //         'size_range' => 'required',
    //         'cost' => 'required|numeric',
    //         'wholesale_price' => 'required|numeric',
    //         'vendor_id' => 'required|exists:vendors,id',
    //         'colors' => 'required|array',
    //         'colors.*' => 'string'
    //     ]);

    //     // Create product
    //     $product = Product::create($validated);

    //     // Create variants
    //     foreach ($request->colors as $color) {
    //         $product->variants()->create([
    //             'color' => $color,
    //             'status' => 1
    //         ]);
    //     }

    //     return redirect()->route('admin.products.index')
    //         ->with('success', 'Product created successfully');
    // }

    // public function edit(ProductVariant $variant)
    // {
    //     $product = $variant->product;
    //     $vendors = Vendor::all();
    //     return view('admin.products.edit', compact('product', 'variant', 'vendors'));
    // }

    // public function update(Request $request, ProductVariant $variant)
    // {
    //     $validated = $request->validate([
    //         'style' => 'required|unique:products,style,'.$variant->product_id,
    //         'image' => 'required|url',
    //         'link' => 'required|url',
    //         'size_range' => 'required',
    //         'cost' => 'required|numeric',
    //         'wholesale_price' => 'required|numeric',
    //         'vendor_id' => 'required|exists:vendors,id',
    //         'color' => 'required|string'
    //     ]);

    //     $variant->product->update($validated);
    //     $variant->update(['color' => $request->color]);

    //     return redirect()->route('admin.products.index')
    //         ->with('success', 'Product updated successfully');
    // }

    // public function destroy(Product $product)
    // {
    //     $product->delete();
    //     return redirect()->back()->with('success', 'Product deleted successfully');
    // }

    // public function batchAction(Request $request)
    // {
    //     $action = $request->action;
    //     $products = $request->products;

    //     if (!$products) {
    //         return redirect()->back()->with('error', 'No products selected');
    //     }

    //     switch ($action) {
    //         case 'delete':
    //             Product::whereIn('style', $products)->delete();
    //             return redirect()->back()->with('success', 'Selected products deleted');

    //         case 'activate':
    //             ProductVariant::whereIn('product_id', 
    //                 Product::whereIn('style', $products)->pluck('id')
    //             )->update(['status' => 1]);
    //             return redirect()->back()->with('success', 'Selected products activated');

    //         case 'deactivate':
    //             ProductVariant::whereIn('product_id', 
    //                 Product::whereIn('style', $products)->pluck('id')
    //             )->update(['status' => 0]);
    //             return redirect()->back()->with('success', 'Selected products deactivated');

    //         default:
    //             return redirect()->back()->with('error', 'Invalid action');
    //     }
    // }

    // public function updateVariantStatus(Request $request)
    // {
    //     $variant = ProductVariant::findOrFail($request->id);
    //     $variant->update(['status' => $request->status]);

    //     return response()->json(['success' => true]);
    // }

    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xls,xlsx'
    //     ]);

    //     Excel::import(new ProductsImport, $request->file('file'));

    //     return redirect()->back()->with('success', 'Products imported successfully');
    // }

    // public function export()
    // {
    //     return Excel::download(new ProductsExport, 'products.xlsx');
    // }

    // public function archive(Request $request)
    // {
    //     // Implement your archive logic here
    //     // This would typically create an archive record and update product statuses

    //     return response()->json(['success' => true]);
    // }
    
    public function toggleInventory(Request $request)
    {
        // dd($request);
        Product::where('product_style', $request->style)
            ->update(['show_from_inventory' => $request->status]);
    
        return response()->json(['success' => true]);
    }
}
