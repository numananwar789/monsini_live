<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use App\Imports\OrderHistoryImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


use App\Models\Order;
use App\Models\OrderFinal;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderAllocation;
use App\Models\Vendor;
use App\Models\OrderHistory;

class OrderHistoryController extends Controller
{
    public function index()
    {


        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        // Check user role
        if (Auth::user()->admin_role == 'customer') {
            return redirect()->route('home');
        }

        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $historyList = OrderHistory::orderBy('history_date', 'DESC')->get();

        return view('order-histories.index', compact('ownerComp', 'historyList'));
    }

    public function archive2(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'selectedItems' => 'required',
            'archiveName' => 'required|string'
        ]);

        // dd($request->selectedItems);
        $selectedItems = json_decode($request->selectedItems);

        //   dd($selectedItems);
        $archiveName = $validated['archiveName'];

        DB::beginTransaction();

        try {
            // Insert into archive table with archive name
            $placeholders = implode(',', $selectedItems);
            $bindings = [$archiveName];

            // dd($placeholders);

            $insertQuery = "
                INSERT INTO dt_order_history_archive (
                    history_ID, allocation_ID, final_ID, order_ID, order_customer_ID, order_customer_name,
                    order_vendor_ID, order_vendor_name, vendor_purchase_ID, order_product_ID,
                    order_product_style, order_product_color, order_product_size, order_quantity,
                    given_by_invntry, given_by_onway, order_cost, order_purchase_price, order_note,
                    purchase_id, created_at, created_at_final, created_at_allocation,
                    onway_vndr_prchs_ids, onway_cstmr_prchs_ids, history_date, order_wear_date,
                    user_flag, order_GUID, archive_name
                )
                SELECT 
                    history_ID, allocation_ID, final_ID, order_ID, order_customer_ID, order_customer_name,
                    order_vendor_ID, order_vendor_name, vendor_purchase_ID, order_product_ID,
                    order_product_style, order_product_color, order_product_size, order_quantity,
                    given_by_invntry, given_by_onway, order_cost, order_purchase_price, order_note,
                    purchase_id, created_at, created_at_final, created_at_allocation,
                    onway_vndr_prchs_ids, onway_cstmr_prchs_ids, history_date, order_wear_date,
                    user_flag, order_GUID, ?
                FROM dt_order_history 
                WHERE history_ID IN ($placeholders)
            ";


            DB::insert($insertQuery, $bindings);

            // Delete from original table
            DB::table('dt_order_history')->whereIn('history_ID', $selectedItems)->delete();

            DB::commit();

            return redirect()->route('order-histories.index')->with('success', 'Archived successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return back()->with('error', 'Failed to archive: ' . $e->getMessage());
        }
    }


    // use Illuminate\Http\Request;
    // use Illuminate\Support\Facades\DB;

    // public function archive(Request $request)
    // {



    //     $validated = $request->validate([
    //         'selectedItems' => 'required',
    //         // 'selectedItems.*' => 'required|integer',
    //         'archiveName' => 'required|string|max:255',
    //     ], [
    //         'selectedItems.required' => 'Please select at least one item to archive.',
    //         'archiveName.required' => 'Archive name is required.',
    //     ]);
    //     $productsChecked = json_decode($request->input('selectedItems'), true);
    //     // $productsChecked = array_filter($request->input('selectedItems'));
    //     $archiveName = $request->input('archiveName');

    //     //    dd($productsChecked);
    //     DB::beginTransaction();

    //     try {
    //         // Fetch matching records
    //         $records = DB::table('dt_order_history')
    //             ->whereIn('history_ID', $productsChecked)
    //             ->get();

    //         // Map data and add archive_name
    //         $archivedData = $records->map(function ($record) use ($archiveName) {
    //             $data = (array) $record;
    //             $data['archive_name'] = $archiveName;
    //             $data['sub_products'] = is_array($data['sub_products']) ? json_encode($data['sub_products']) : $data['sub_products'];
    //             return $data;
    //         })->toArray();

    //         // Insert into archive table
    //         DB::table('dt_order_history_archive')->insert($archivedData);

    //         // Delete from original table
    //         DB::table('dt_order_history')
    //             ->whereIn('history_ID', $productsChecked)
    //             ->delete();

    //         DB::commit();

    //         return redirect()->back()->with('success', 'Selected items archived successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->with('error', 'Failed to archive items: ' . $e->getMessage());
    //     }
    // }
    
public function archive(Request $request)
{
    $validated = $request->validate([
        'selectedItems' => 'required',
        'archiveName'  => 'required|string|max:255',
    ], [
        'selectedItems.required' => 'Please select at least one item to archive.',
        'archiveName.required'   => 'Archive name is required.',
    ]);

    ini_set('memory_limit', '512M');
    set_time_limit(300);

    $productsChecked = json_decode($request->input('selectedItems'), true);

    if (!is_array($productsChecked) || empty($productsChecked)) {
        return redirect()->back()
            ->with('error', 'Invalid selected items.');
    }

    $archiveName = trim($request->input('archiveName'));

    // Datetime columns that may contain invalid zero-dates
    $datetimeColumns = ['created_at', 'created_at_allocation', 'created_at_final'];

    try {
        foreach (array_chunk($productsChecked, 200) as $chunkIds) {
            DB::beginTransaction();
            try {
                $records = DB::table('dt_order_history')
                    ->whereIn('history_ID', $chunkIds)
                    ->get();

                if ($records->isEmpty()) {
                    DB::commit();
                    continue;
                }

                $archivedData = [];

                foreach ($records as $record) {
                    $data = (array) $record;

                    unset($data['history_ID']);

                    $data['archive_name'] = $archiveName;

                    // Sanitize zero-dates to NULL
                    foreach ($datetimeColumns as $col) {
                        if (
                            isset($data[$col]) &&
                            (
                                $data[$col] === '0000-00-00 00:00:00' ||
                                $data[$col] === '0000-00-00' ||
                                $data[$col] === '' ||
                                $data[$col] === null
                            )
                        ) {
                            $data[$col] = null;
                        }
                    }

                    // Ensure sub_products is valid JSON
                    if (isset($data['sub_products'])) {
                        if (is_array($data['sub_products'])) {
                            $data['sub_products'] = json_encode($data['sub_products']);
                        }
                        if ($data['sub_products'] === '?' || $data['sub_products'] === '') {
                            $data['sub_products'] = json_encode([]);
                        }
                    }

                    $archivedData[] = $data;
                }

                if (!empty($archivedData)) {
                    DB::table('dt_order_history_archive')
                        ->insert($archivedData);

                    DB::table('dt_order_history')
                        ->whereIn('history_ID', $chunkIds)
                        ->delete();
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return redirect()->back()
            ->with('success', 'Selected items archived successfully.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Failed to archive items: ' . $e->getMessage());
    }
}


    public function destroy($id)
    {
        $orderID = $id;
        try {

            $ownerComp = DB::table('dt_cust')
                ->where('cust_owner', 'Yes')
                ->value('cust_comp_name') ?? '';

            $productId = DB::table('dt_order_history')->where('order_ID', $orderID)->value('order_product_ID');
            $orderCust = DB::table('dt_order_history')->where('order_ID', $orderID)->value('order_customer_name');

            $deletedHistory = DB::table('dt_order_history')->where('order_ID', $orderID)->delete();


            if ($deletedHistory) {
                $deletedOrder = DB::table('dt_order')->where('order_ID', $orderID)->delete();

                if ($deletedOrder && trim($ownerComp) == trim($orderCust)) {
                    DB::table('dt_inventory')->where('product_ID', $productId)->delete();
                }

                DB::commit();
                return response()->json(['success' => 1]);
            }

            DB::rollBack();
            return response()->json(['error' => 'Invalid ID'], 400);
            return response()->json(['success' => 1]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $import = new OrderHistoryImport();
        Excel::import($import, $request->file('file'));

        $message = "$import->cnfrm_order_count/$import->order_count orders imported successfully";

        if (count($import->errors)) {
            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $import->errors);
        }

        return redirect()->route('order-histories.index')
            ->with('success', $message);
    }


    public function edit($id)
    {
        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $order = OrderHistory::where('order_ID', $id)->first();

        // dd($order);
        // Get related data
        $colors = Product::where('product_style', $order->order_product_style)
            ->distinct('product_color')
            ->pluck('product_color');

        $sizeRange = Product::where('product_style', $order->order_product_style)
            ->value('product_size_range');

        $costProduct = Product::where('product_style', $order->order_product_style)
            ->value('product_wholesale_price');

        // Get dropdown options
        $vendors = Vendor::select('vendor_ID', 'vendor_comp_name')->get();
        $customers = Customer::select('cust_ID', 'cust_comp_name')->orderBy('cust_comp_name')->get();
        $products = Product::select('product_style', 'product_vendor_name')->distinct()->get();
        $productStyles = Product::select('product_style')->distinct()->get();
        $vendorProducts = Product::select('product_style', 'product_vendor_name', 'product_vendor_ID')->distinct()->get();


        $product = Product::where('product_style',  $order->order_product_style)->first();
        $sub_products = $product && $product->sub_products ? $product->sub_products : [];
        $subProducts = is_string($sub_products)
            ? json_decode($sub_products, true)
            : $sub_products;

        return view('order-histories.edit', compact(
            'order',
            'ownerComp',
            'colors',
            'sizeRange',
            'costProduct',
            'vendors',
            'customers',
            'products',
            'productStyles',
            'subProducts',
            'vendorProducts'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'customers' => 'required',
            'style' => 'required',
            'color' => 'required',
            'size' => 'required',
            'quantity' => 'required|numeric',
            'purchase_id' => 'required',
            'vpID' => 'nullable',
            'note' => 'nullable'
        ]);

        // Get product info
        $product = Product::where('product_style', strtolower($request->style))
            ->where('product_color', $request->color)
            ->firstOrFail();

        // Get customer info
        $customer = Customer::where('cust_comp_name', $request->customers)->firstOrFail();

        // Calculate costs
        $orderCost = $request->size < 18
            ? $request->quantity * $product->product_wholesale_price
            : $request->quantity * ($product->product_wholesale_price + 30);

        $orderPurchasePrice = $request->quantity * $product->product_cost;

        // Update order in all tables
        $orderData = [
            'order_customer_ID' => $customer->cust_ID,
            'order_customer_name' => $customer->cust_comp_name,
            'order_vendor_ID' => $product->product_vendor_ID,
            'order_vendor_name' => $product->product_vendor_name,
            'order_product_ID' => $product->product_ID,
            'order_product_style' => $request->style,
            'order_product_color' => $request->color,
            'order_product_size' => $request->size,
            'order_quantity' => $request->quantity,
            'order_cost' => $orderPurchasePrice,
            'order_purchase_price' => $orderCost,
            'order_note' => $request->note,
            'sub_products' => $request->sub_products,
            'purchase_id' => $request->purchase_id
        ];


        $allocationData = array_merge($orderData, [
            'vendor_purchase_ID' => $request->vpID
        ]);

        // Update in all relevant tables
        OrderHistory::where('order_ID', $id)->update($orderData);
        OrderAllocation::where('order_ID', $id)->update($allocationData);
        OrderFinal::where('order_ID', $id)->update($orderData);
        Order::where('order_ID', $id)->update($orderData);

        return redirect()->route('order-histories.index')->with('success', 'Order updated successfully!');
    }
}
