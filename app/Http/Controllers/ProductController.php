<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductArchive;
use App\Models\Vendor;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Imports\ProductsImport;
use Shuchkin\SimpleXLSXGen;
use DB;

class ProductController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        // NOTE: product rows are no longer fetched here. The table now loads
        // its rows via AJAX from getProductsData() using server-side
        // DataTables processing, so we don't pull every product on page load.

        $years = DB::table('dt_product')
            ->select('version_year as year')
            ->groupBy('version_year')
            ->orderBy('version_year', 'desc')
            ->get();

        foreach ($years as $y) {
            $y->count = DB::table('dt_product')
                ->where('version_year', $y->year)
                ->count();

            $y->is_published = DB::table('dt_product_year_control')
                ->where('year', $y->year)
                ->value('is_published') ?? 0;
        }

        $allSubProducts = \App\Models\SubProduct::pluck('sub_product_name')->toArray();
        return view('products.index', compact('allSubProducts', 'years'));
    }

    /**
     * AJAX endpoint consumed by the DataTables "serverSide" config on
     * products.index. Handles paging, global search, and column sorting
     * in SQL rather than loading every product row on every page view.
     */
    public function getProductsData(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim((string) $request->input('search.value', ''));
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Maps DataTables column index -> sortable DB column.
        // Index 0/1/5/10 (checkbox/image/sub_products/actions) are not sortable.
        $sortableColumns = [
            2 => 'product_style',
            3 => 'factory_style',
            4 => 'product_color',
            6 => 'product_size_range',
            7 => 'product_cost',
            8 => 'product_wholesale_price',
            9 => 'product_vendor_name',
        ];

        $grouped = Product::selectRaw('
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
            ->groupBy('product_style');

        $recordsTotal = DB::query()->fromSub($grouped, 'grouped_products')->count();

        $query = DB::query()->fromSub($grouped, 'grouped_products');

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('product_style', 'like', "%{$searchValue}%")
                    ->orWhere('factory_style', 'like', "%{$searchValue}%")
                    ->orWhere('product_color', 'like', "%{$searchValue}%")
                    ->orWhere('product_vendor_name', 'like', "%{$searchValue}%")
                    ->orWhere('product_size_range', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        if ($orderColumnIndex !== null && isset($sortableColumns[$orderColumnIndex])) {
            $query->orderBy($sortableColumns[$orderColumnIndex], $orderDir);
        } else {
            $query->orderBy('product_style', 'asc');
        }

        if ($length != -1) {
            $query->offset($start)->limit($length);
        }

        $rows = $query->get();

        // Batch-load color variants for just the styles on this page,
        // instead of running a query per row like the old Blade @foreach did.
        $styles = $rows->pluck('product_style')->all();
        $colorsByStyle = collect();
        if (!empty($styles)) {
            $colorsByStyle = Product::whereIn('product_style', $styles)
                ->get(['product_ID', 'product_style', 'product_color', 'product_status'])
                ->groupBy('product_style');
        }

        $canEdit = auth()->user()->admin_role === 'superadmin' || auth()->user()->user_name == 'admin1';

        $data = [];
        foreach ($rows as $row) {
            $colors = $colorsByStyle->get($row->product_style, collect());

            $colorOptions = '';
            foreach ($colors as $c) {
                $selected = $c->product_status ? 'selected' : '';
                $colorOptions .= '<option ' . $selected . ' value="' . e($c->product_ID) . '" data-style="' . e($row->product_style) . '" data-colorId="' . e($c->product_ID) . '">'
                    . strtoupper(e($c->product_color)) . '</option>';
            }

            $prodStatusNow = $colors->where('product_status', 1)->count();

            $subProducts = '';
            if (!empty($row->sub_products)) {
                $decoded = json_decode($row->sub_products, true);
                if (is_array($decoded)) {
                    $subProducts = implode(', ', $decoded);
                }
            }

            $actions = '';
            if ($canEdit) {
                $editUrl = route('products.edit', $row->product_ID);
                $actionUrl = route('admin-products.action');

                $actions .= '<a target="_self" class="btn btn-success mb-0 btn-sm edit_product btn-width" href="' . $editUrl . '">Edit</a>';
                $actions .= '<form method="POST" action="' . $actionUrl . '" class="d-inline product-action-form">';
                $actions .= csrf_field();
                $actions .= '<input type="hidden" name="styleNumber" value="' . e($row->product_style) . '">';
                $actions .= '<input type="submit" name="action" class="btn btn-danger mb-0 btn-sm btn-width" value="Delete">';

                if ($prodStatusNow >= 1) {
                    $actions .= '<input type="submit" name="action" class="btn btn-success mb-0 btn-sm btn-width" value="Active">';
                } else {
                    $actions .= '<input type="submit" name="action" class="btn btn-warning mb-0 btn-sm btn-width" value="Inactive">';
                }

                $disabled = $prodStatusNow >= 1 ? 'disabled' : '';
                $checked = $row->inventory_override ? 'checked' : '';
                $actions .= '<label><input type="checkbox" class="toggle-inventory-override" data-style="' . e($row->product_style) . '" ' . $checked . ' ' . $disabled . '> Show from Inventory</label>';
                $actions .= '</form>';
            }

            $data[] = [
                'checkbox' => '<input class="form-check-input" type="checkbox" value="' . e($row->product_style) . '" id="chk_' . e($row->product_style) . '" name="products[]"><label class="form-check-label" for="chk_' . e($row->product_style) . '"></label>',
                'image' => '<div class="col-12 col-md-4 mx-auto" style="padding:0px;"><img src="' . e($row->product_image) . '" alt="" class="w-100 img-fluid zoom"></div>',
                'style' => '<a target="_blank" href="' . e($row->product_link) . '">' . strtoupper(e($row->product_style)) . '</a>',
                'factory_style' => strtoupper(e($row->factory_style)),
                'color' => '<select class="js-select2 form-control select_color" name="select_color" multiple>' . $colorOptions . '</select>',
                'sub_products' => e($subProducts),
                'size_range' => e($row->product_size_range),
                'cost' => e($row->product_cost),
                'price' => e($row->product_wholesale_price),
                'vendor' => strtoupper(e($row->product_vendor_name)),
                'actions' => $actions,
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function action(Request $request)
    {
        $action = $request->input('action');
        $styleNumber = rtrim($request->input('styleNumber'), ' ');
        $id = $request->input('id');

        if ($styleNumber) {
            $message = null;
            $deleted = null;

            switch ($action) {
                case 'Delete':
                    $deleted = Product::where('product_style', $styleNumber)->delete();
                    $message = $deleted
                        ? "Product \"{$styleNumber}\" deleted successfully."
                        : "Product \"{$styleNumber}\" was not found or could not be deleted.";
                    break;
                case 'Active':
                    Product::where('product_style', $styleNumber)
                        ->update(['product_status' => 0]);
                    $message = "Product \"{$styleNumber}\" marked inactive.";
                    break;
                case 'Inactive':
                    Product::where('product_style', $styleNumber)
                        ->update(['product_status' => 1]);
                    $message = "Product \"{$styleNumber}\" marked active.";
                    break;
                default:
                    return redirect()->route('products.index')
                        ->with('error', 'Unknown action requested.');
            }

            $flashType = ($action === 'Delete' && !$deleted) ? 'error' : 'success';

            return redirect()->route('products.index')
                ->with($flashType, $message);
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
            'version_year' => 'required|numeric',
        ]);

        $existingProduct = Product::where('product_style', strtolower($validated['style']))
            ->where('product_color', strtolower($validated['color']))
            ->first();

        if ($existingProduct) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Product with this style and color already exists!');
        }

        $vendor = Vendor::findOrFail($validated['vendor_id']);

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
            'version_year' => $validated['version_year'],
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

            $products = Product::whereIn('product_style', $validated['selectedItems'])->get();

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
                    'version_year' => $product->version_year,
                    'product_status' => 1,
                    'archive_name' => $validated['archiveName']
                ]);
            }

            Product::whereIn('product_style', $validated['selectedItems'])->delete();
        });

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $vendors = Vendor::all();
        $allSubProducts = \App\Models\SubProduct::pluck('sub_product_name')->toArray();
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
            'version_year' => 'required|numeric',
        ]);

        $product = Product::findOrFail($id);
        $vendor = Vendor::findOrFail($validated['vendor_id']);

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
                'product_image' => $validated['image'] ?? "",
                'version_year' => $validated['version_year']
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
        $products = Product::all();

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

        $data = [$headers];

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

        $filename = 'products_bkp_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return SimpleXLSXGen::fromArray($data)
            ->downloadAs($filename);
    }

    public function getWholesalePrice(Request $request)
    {
        $style = $request->input('style');

        $price = Product::where('product_style', $style)
            ->value('product_wholesale_price');

        return response()->json($price);
    }

    public function toggleInventory(Request $request)
    {
        Product::where('product_style', $request->style)
            ->update(['show_from_inventory' => $request->status]);

        return response()->json(['success' => true]);
    }

    public function toggleYear(Request $request)
    {
        DB::table('dt_product_year_control')
            ->updateOrInsert(
                ['year' => $request->year],
                [
                    'is_published' => (int) $request->status,
                    'updated_at' => now()
                ]
            );

        $isPublished = (int) $request->status === 1;

        return response()->json([
            'success' => true,
            'year' => $request->year,
            'status' => $request->status,
            'message' => $isPublished
                ? 'Products published !'
                : 'Products unpublished !'
        ]);
    }
}
