<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\OrderAllocation;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');

        $inventoryList = Inventory::where('product_quantity', '>', 0)
            ->whereNotNull('product_quantity')
            ->where('product_quantity', '<>', ' ')
            ->get();

        $onwayList = OrderAllocation::where('order_customer_name', $ownerComp)
            ->where('order_quantity', '>', 0)
            ->get();
        
        $totalInventoryCount = Inventory::where('product_quantity', '>', 0)
            ->whereNotNull('product_quantity')
            ->where('product_quantity', '<>', ' ')
            // ->sum('product_quantity');
            ->count();
        
        // $totalOnWayCount = OrderAllocation::where('order_customer_name', $ownerComp)
        //     ->sum('order_quantity');
        
        $totalOnWayCount = OrderAllocation::where('order_status', '!=', 'Allocated')
            ->where('order_customer_name', $ownerComp)
            // ->count();
            ->sum('order_quantity') ?? 0;

        return view('inventory.index', compact(
            'ownerComp',
            'inventoryList',
            'onwayList',
            'totalInventoryCount',
            'totalOnWayCount',
        ));
    }


    public function create()
    {
        // Authentication check
        if (!Auth::check() || Auth::user()->admin_role === 'customer') {
            return redirect()->route('home');
        }

        $productStyles = Product::distinct('product_style')->pluck('product_style');

        return view('inventory.create', compact('productStyles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'style' => 'required|string',
            'color' => 'required|string',
            'size' => 'required|integer',
            'quantity' => 'required|integer|min:1'
        ]);

        // Get product details
        $product = Product::where('product_style', $request->style)
            ->where('product_color', $request->color)
            ->firstOrFail();

        // Create inventory record
        Inventory::create([
            'product_ID' => $product->product_ID,
            'product_style' => $product->product_style,
            'product_color' => $product->product_color,
            'product_size' => $request->size,
            'product_cost' => $product->product_cost,
            'product_wholesale_price' => $product->product_wholesale_price,
            'product_vendor_ID' => $product->product_vendor_ID,
            'product_vendor_name' => $product->product_vendor_name,
            'product_link' => $product->product_link,
            'product_image' => $product->product_image,
            'product_quantity' => $request->quantity
        ]);

        return redirect()->route('inventories.index')->with('success', 'Inventory added successfully');
    }

    // AJAX methods for color and size dropdowns
    public function getColors(Request $request)
    {
        $colors = Product::where('product_style', $request->style)
            ->distinct('product_color')
            ->pluck('product_color');

        $options = '<option value="">Choose A Color</option>';
        foreach ($colors as $color) {
            $options .= '<option value="' . e($color) . '">' . strtoupper(e($color)) . '</option>';
        }

        return $options;
    }
    public function getSubProducts($style)
    {
        $product = Product::where('product_style', $style)->first();
        $sub_products = $product && $product->sub_products ? $product->sub_products : [];
        $subProducts = is_string($sub_products)
            ? json_decode($sub_products, true)
            : $sub_products;



        $options = "";
        if (is_array($subProducts)) {
            foreach ($subProducts as $sub) {
                $options .= '<option value="' . e($sub) . '">' . strtoupper(e($sub)) . '</option>';
            }
        }

        return $options;
    }

    public function getColors2(Request $request)
    {
        $request->validate([
            'style' => 'required|string'
        ]);

        $colors = Product::where('product_style1', $request->style)
            ->distinct('product_color')
            ->pluck('product_color');

        dd($request->style);
        $options = '<option value="">Choose A Color</option>';
        foreach ($colors as $color) {
            $trimmedColor = trim($color);
            $options .= '<option value="' . e($trimmedColor) . '">' . strtoupper(e($trimmedColor)) . '</option>';
        }

        return $options;
    }

    // public function getSizes(Request $request)
    // {
    //     $product = Product::where('product_style', $request->style_get)
    //         ->where('product_color', $request->color_get)
    //         ->first();

    //     if (!$product) {
    //         return response()->json(['min' => 0, 'max' => 0]);
    //     }

    //     $sizeRange = explode('-', $product->product_size_range);

    //     return response()->json([
    //         'min' => $sizeRange[0],
    //         'max' => $sizeRange[1]
    //     ]);
    // }
    
    public function getSizes(Request $request)
{
    $product = Product::where('product_style', $request->style_get)
        ->where('product_color', $request->color_get)
        ->first();

    if (!$product || !$product->product_size_range) {
        return response()->json([
            'sizes' => []
        ]);
    }

    $range = explode('-', $product->product_size_range);

    $originalMin = isset($range[0]) ? trim($range[0]) : '0';
    $min = (int) $range[0];
    $max = (int) $range[1];

    $sizes = [];

    // ==========================================
    // CASE: 00-26 → 00, 0, 2, 4, ...
    // ==========================================
    if ($originalMin === '00') {

        $sizes[] = '00';

        for ($i = 0; $i <= $max; $i += 2) {
            $sizes[] = (string) $i;
        }
    }

    // ==========================================
    // CASE: 0-26 → 0, 2, 4, ...
    // ==========================================
    else {
        for ($i = $min; $i <= $max; $i += 2) {
            $sizes[] = (string) $i;
        }
    }

    return response()->json([
        'sizes' => $sizes
    ]);
}

    public function edit($id)
    {
        // Authentication check


        $inventory = Inventory::findOrFail($id);
        $productStyles = Product::distinct('product_style')->pluck('product_style');

        return view('inventory.edit', compact('inventory', 'productStyles'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            // 'size' => 'required|integer',
            'size' => 'required',
            'color' => 'required|string'
        ]);

        $inventory = Inventory::findOrFail($id);
        $inventory->update([
            'product_quantity' => $request->quantity,
            'product_size' => $request->size,
            'product_color' => $request->color
        ]);

        return redirect()->route('inventories.index')->with('success', 'Inventory updated successfully');
    }

    // AJAX method to get colors


    public function destroy($id)
    {
        try {
            DB::table('dt_inventory')->where('uID', $id)->delete();
            return redirect()->route('inventories.index')->with('success', 'Inventory deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
