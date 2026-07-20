<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustProductController extends Controller
{
    public function index()
    {
        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
    
        $products = Product::leftJoin('dt_inventory', 'dt_product.product_ID', '=', 'dt_inventory.product_ID')
            ->leftJoin('dt_product_year_control as pyc', 'pyc.year', '=', 'dt_product.version_year')
            ->selectRaw("
                dt_product.product_style,
            
                MAX(dt_product.product_ID) as product_ID,
                MAX(dt_product.product_image) as product_image,
                MAX(dt_product.factory_style) as factory_style,
                MAX(dt_product.product_vendor_name) as product_vendor_name,
                MAX(dt_product.product_link) as product_link,
                MAX(dt_product.product_cost) as product_cost,
                MAX(dt_product.product_wholesale_price) as product_wholesale_price,
                MAX(dt_product.product_status) as product_status,
                MAX(dt_product.sub_products) as sub_products,
                MAX(dt_product.show_from_inventory) as inventory_override,
            
                CASE 
                    WHEN MAX(dt_product.product_status) = 0 
                         AND MAX(dt_product.show_from_inventory) = 1 
                    THEN 1 
                    ELSE 0 
                END as use_inventory,
            
                COALESCE(SUM(
                    CASE 
                        WHEN dt_inventory.product_quantity > 0 
                        THEN dt_inventory.product_quantity 
                        ELSE 0 
                    END
                ), 0) as inventory_count,
            
                (
                    SELECT COALESCE(SUM(dtoa.order_quantity), 0)
                    FROM dt_order_allocation dtoa
                    WHERE dtoa.order_product_style = dt_product.product_style
                    AND dtoa.order_customer_name = '{$ownerComp}'
                    AND dtoa.order_quantity > 0
                ) as onway_count,
            
                (
                    COALESCE(SUM(
                        CASE 
                            WHEN dt_inventory.product_quantity > 0 
                            THEN dt_inventory.product_quantity 
                            ELSE 0 
                        END
                    ), 0)
            
                    +
            
                    (
                        SELECT COALESCE(SUM(dtoa.order_quantity), 0)
                        FROM dt_order_allocation dtoa
                        WHERE dtoa.order_product_style = dt_product.product_style
                        AND dtoa.order_customer_name = '{$ownerComp}'
                        AND dtoa.order_quantity > 0
                    )
                ) as total_available_quantity,
            
                CASE
                    WHEN MAX(dt_product.product_status) = 1 THEN 1
                    WHEN MAX(dt_product.product_status) = 0 
                         AND MAX(dt_product.show_from_inventory) = 1 
                         AND MAX(CASE 
                             WHEN dt_inventory.product_quantity > 0 THEN 1 
                             ELSE 0 
                         END) = 1
                    THEN 1
                    ELSE 0
                END as is_available,
            
                -- GROUP_CONCAT(DISTINCT dt_product.product_color) as all_colors,
                GROUP_CONCAT(DISTINCT 
                    CASE 
                        WHEN dt_product.product_status = 1 
                        THEN dt_product.product_color
                    END
                ) as all_colors,
                
                GROUP_CONCAT(DISTINCT dt_product.product_size_range) as all_sizes,
            
                GROUP_CONCAT(DISTINCT 
                    CASE 
                        WHEN dt_inventory.product_quantity > 0 
                        THEN dt_inventory.product_color 
                    END
                ) as in_stock_colors,
            
                GROUP_CONCAT(DISTINCT 
                    CASE 
                        WHEN dt_inventory.product_quantity > 0 
                        THEN dt_inventory.product_size 
                    END
                ) as in_stock_sizes
            ")
            ->where(function ($q) {
                $q->where('pyc.is_published', 1);
            })
            ->groupBy('dt_product.product_style')
            ->havingRaw('
                MAX(dt_product.product_status) = 1
                OR (
                    MAX(dt_product.product_status) = 0 
                    AND MAX(dt_product.show_from_inventory) = 1
                    AND MAX(CASE 
                        WHEN dt_inventory.product_quantity > 0 THEN 1 
                        ELSE 0 
                    END) = 1
                )
            ')
            ->orderBy('dt_product.product_style')
            ->get();
    
        return view('customer.products', compact('products', 'ownerComp'));
    }

    public function store(Request $request)
    {
        // Validate the request

        // dd($request->all());
        $validated = $request->validate([
            'prod_style' => 'required|array',
            'color' => 'required|array',
            'size' => 'required|array',
            'quantsOrder' => 'required|array',
            'purchase_id' => 'required|array',
            'wearDate' => 'required|array',
        ]);

        // Get arrays from the request
        $prodStyles = array_filter($request->prod_style);
        $colorsNow = array_filter($request->color);
        $sizeNow = array_filter($request->size);
        $quantNow = array_filter($request->quantsOrder);
        $purNow = array_filter($request->purchase_id);
        $wearDates = array_filter($request->wearDate);
        $subProducts = $request->sub_products;

        // Check if all arrays have the same count

       
        $countArray = [
            count($colorsNow),
            count($sizeNow),
            count($quantNow),
            count($purNow),
            count($wearDates)
        ];

        if (count(array_unique($countArray)) !== 1) {
            return back()->with('error', 'You are missing some quantities, purchase IDs, wear dates, or color/size selections.');
        }

        // Get customer info
        $customer = Auth::user()->customer();
        $customer = Customer::where('user_id', Auth::user()->id)->first();

        // dd($customer);
        $customer_ID = $customer->cust_ID;
        $customerName = $customer->cust_comp_name;
        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');

        // Check for duplicate purchase IDs
        $prevPurIDCount = 0;
        $errPurID = "";

        foreach ($colorsNow as $key => $color) {
            $prodPur = $purNow[$key];
            $prevPurCheck = Order::where('purchase_id', $prodPur)
                ->where('order_customer_ID', $customer_ID)
                ->where('purchase_ID', '!=', 'STOCK')
                ->count();

            if ($prevPurCheck >= 1) {
                $prevPurIDCount += 1;
                $errPurID .= $prodPur . ",";
            }
        }

        if ($prevPurIDCount > 0) {
            $errMsg = "Duplicate purchase ID(s). Please use a different one. \nDuplicate ID(s): " . rtrim($errPurID, ',');
            return back()->with('error', $errMsg);
        }

        // dd($subProducts);

        // Process each order
        foreach ($colorsNow as $key => $color) {
            $prodStyle = $prodStyles[$key];
            $prodColor = $colorsNow[$key];
            $prodSize = $sizeNow[$key] == "0.01" ? "0" : $sizeNow[$key];
            $prodQuant = $quantNow[$key];
            $prodPur = $purNow[$key];
            $wearDateNow = $wearDates[$key];

            // Get product info
            $prodInfo = Product::where('product_style', strtolower($prodStyle))
                ->where('product_color', $prodColor)
                ->first();

            if (!$prodInfo) {
                continue;
            }


            $sub_products = null;

            if(isset($subProducts[$prodStyle]) && $subProducts[$prodStyle])
            {
                $sub_products = $subProducts[$prodStyle];
            }


            $vendorID = $prodInfo->product_vendor_ID;
            $vendorName = $prodInfo->product_vendor_name;
            $productID = $prodInfo->product_ID;
            $orderCost = $prodSize < 18
                ? $prodQuant * $prodInfo->product_wholesale_price
                : $prodQuant * ($prodInfo->product_wholesale_price + 30);

            $orderPurchasePrice = $prodQuant * $prodInfo->product_cost;
            $orderNote = "NA";
            $onwayVendorPurId = "NA";
            $onwayCustPurId = "NA";

            // Check inventory count
            $inventoryCount = Inventory::where('product_style', $prodStyle)
                ->where('product_color', $prodColor)
                ->where('product_size', $prodSize)
                ->sum('product_quantity');

            // Check on way count
            $onWayCount = OrderAllocation::where('order_product_style', $prodStyle)
                ->where('order_product_color', $prodColor)
                ->where('order_product_size', $prodSize)
                ->where('order_customer_name', $ownerComp)
                ->where('order_quantity', '>', 0)
                ->sum('order_quantity');

            if (($inventoryCount == 0 && $onWayCount == 0) || $customer->username == $ownerComp) {
                // Insert simple order
                Order::create([
                    'order_customer_ID' => $customer_ID,
                    'order_customer_name' => $customerName,
                    'order_vendor_ID' => $vendorID,
                    'order_vendor_name' => $vendorName,
                    'order_product_ID' => $productID,
                    'order_product_style' => $prodStyle,
                    'order_product_color' => $prodColor,
                    'order_product_size' => $prodSize,
                    'order_quantity' => $prodQuant,
                    'order_cost' => $orderPurchasePrice,
                    'order_purchase_price' => $orderCost,
                    'order_note' => $orderNote,
                    'purchase_id' => $prodPur,
                    'onway_vndr_prchs_ids' => $onwayVendorPurId,
                    'onway_cstmr_prchs_ids' => $onwayCustPurId,
                    'order_wear_date' => $wearDateNow,
                    'sub_products' => $sub_products,
                    'user_flag' => 'customer',
                    'order_GUID' => rand(10000, 99999) . date('YmdHis')
                ]);

                continue;
            }

            // Handle inventory items
            if ($inventoryCount > 0 && $prodQuant > 0) {
                if ($inventoryCount > $prodQuant) {
                    // Insert order with inventory
                    $this->createOrderWithInventory(
                        $customer_ID,
                        $customerName,
                        $vendorID,
                        $vendorName,
                        $productID,
                        $prodStyle,
                        $prodColor,
                        $prodSize,
                        $prodQuant,
                        $prodPur,
                        $wearDateNow,
                        $prodInfo,
                        $prodQuant,
                        $sub_products
                    );

                    // Update inventory
                    Inventory::where('product_style', $prodStyle)
                        ->where('product_color', $prodColor)
                        ->where('product_size', $prodSize)
                        ->decrement('product_quantity', $prodQuant);

                    $prodQuant = 0;
                } elseif ($inventoryCount < $prodQuant) {
                    // Insert partial order from inventory
                    $this->createOrderWithInventory(
                        $customer_ID,
                        $customerName,
                        $vendorID,
                        $vendorName,
                        $productID,
                        $prodStyle,
                        $prodColor,
                        $prodSize,
                        $inventoryCount,
                        $prodPur,
                        $wearDateNow,
                        $prodInfo,
                        $inventoryCount,
                        $sub_products
                    );

                    // Update inventory
                    Inventory::where('product_style', $prodStyle)
                        ->where('product_color', $prodColor)
                        ->where('product_size', $prodSize)
                        ->update(['product_quantity' => 0]);

                    $prodQuant -= $inventoryCount;
                } else {
                    // Insert full order from inventory
                    $this->createOrderWithInventory(
                        $customer_ID,
                        $customerName,
                        $vendorID,
                        $vendorName,
                        $productID,
                        $prodStyle,
                        $prodColor,
                        $prodSize,
                        $prodQuant,
                        $prodPur,
                        $wearDateNow,
                        $prodInfo,
                        $prodQuant,
                        $sub_products
                    );

                    // Update inventory
                    Inventory::where('product_style', $prodStyle)
                        ->where('product_color', $prodColor)
                        ->where('product_size', $prodSize)
                        ->update(['product_quantity' => 0]);

                    $prodQuant = 0;
                }
            }

            // Handle on-way items
            if ($onWayCount > 0 && $prodQuant > 0) {
                $onWayOrders = OrderAllocation::where('order_product_style', $prodStyle)
                    ->where('order_product_color', $prodColor)
                    ->where('order_product_size', $prodSize)
                    ->where('order_customer_name', $ownerComp)
                    ->where('order_quantity', '>', 0)
                    ->orderByDesc('order_quantity')
                    ->get();

                foreach ($onWayOrders as $orderNow) {
                    if ($orderNow->order_quantity > $prodQuant && $prodQuant > 0) {
                        // Insert partial order from on-way
                        $this->createOrderWithOnWay(
                            $customer_ID,
                            $customerName,
                            $vendorID,
                            $vendorName,
                            $productID,
                            $prodStyle,
                            $prodColor,
                            $prodSize,
                            $prodQuant,
                            $prodPur,
                            $wearDateNow,
                            $prodInfo,
                            $prodQuant,
                            $orderNow,
                            $sub_products
                        );

                        // Update allocation
                        OrderAllocation::where('order_ID', $orderNow->order_ID)
                            ->decrement('order_quantity', $prodQuant);

                        $prodQuant = 0;
                    } elseif ($orderNow->order_quantity < $prodQuant && $prodQuant > 0) {
                        // Insert full order from on-way
                        $this->createOrderWithOnWay(
                            $customer_ID,
                            $customerName,
                            $vendorID,
                            $vendorName,
                            $productID,
                            $prodStyle,
                            $prodColor,
                            $prodSize,
                            $orderNow->order_quantity,
                            $prodPur,
                            $wearDateNow,
                            $prodInfo,
                            $orderNow->order_quantity,
                            $orderNow,
                            $sub_products
                        );

                        // Delete related records
                        Order::where('order_ID', $orderNow->order_ID)->delete();
                        // Assuming there's an OrderFinal model
                        // OrderFinal::where('order_ID', $orderNow->order_ID)->delete();
                        $orderNow->delete();

                        $prodQuant -= $orderNow->order_quantity;
                    } elseif ($orderNow->order_quantity == $prodQuant && $prodQuant > 0) {
                        // Insert exact order from on-way
                        $this->createOrderWithOnWay(
                            $customer_ID,
                            $customerName,
                            $vendorID,
                            $vendorName,
                            $productID,
                            $prodStyle,
                            $prodColor,
                            $prodSize,
                            $prodQuant,
                            $prodPur,
                            $wearDateNow,
                            $prodInfo,
                            $prodQuant,
                            $orderNow,
                            $sub_products
                        );

                        // Delete related records
                        Order::where('order_ID', $orderNow->order_ID)->delete();
                        // OrderFinal::where('order_ID', $orderNow->order_ID)->delete();
                        $orderNow->delete();

                        $prodQuant = 0;
                    }
                }
            }

            // Insert remaining as normal order
            if ($prodQuant > 0) {
                Order::create([
                    'order_customer_ID' => $customer_ID,
                    'order_customer_name' => $customerName,
                    'order_vendor_ID' => $vendorID,
                    'order_vendor_name' => $vendorName,
                    'order_product_ID' => $productID,
                    'order_product_style' => $prodStyle,
                    'order_product_color' => $prodColor,
                    'order_product_size' => $prodSize,
                    'order_quantity' => $prodQuant,
                    'order_cost' => $prodQuant * $prodInfo->product_cost,
                    'order_purchase_price' => $prodSize < 18
                        ? $prodQuant * $prodInfo->product_wholesale_price
                        : $prodQuant * ($prodInfo->product_wholesale_price + 30),
                    'order_note' => $orderNote,
                    'purchase_id' => $prodPur,
                    'onway_vndr_prchs_ids' => $onwayVendorPurId,
                    'onway_cstmr_prchs_ids' => $onwayCustPurId,
                    'order_wear_date' => $wearDateNow,
                    'sub_products' => $sub_products,
                    'user_flag' => 'customer',
                    'order_GUID' => rand(10000, 99999) . date('YmdHis')
                ]);
            }
        }

        return redirect()->route('customer.products.index')->with('success', 'Orders placed successfully!');
    }

    private function createOrderWithInventory(
        $customer_ID,
        $customerName,
        $vendorID,
        $vendorName,
        $productID,
        $prodStyle,
        $prodColor,
        $prodSize,
        $quantity,
        $prodPur,
        $wearDateNow,
        $productInfo,
        $givenByInventory,
        $sub_products
    ) {
        $orderCost = $prodSize < 18
            ? $quantity * $productInfo->product_wholesale_price
            : $quantity * ($productInfo->product_wholesale_price + 30);

        $orderPurchasePrice = $quantity * $productInfo->product_cost;

        return Order::create([
            'order_customer_ID' => $customer_ID,
            'order_customer_name' => $customerName,
            'order_vendor_ID' => $vendorID,
            'order_vendor_name' => $vendorName,
            'order_product_ID' => $productID,
            'order_product_style' => $prodStyle,
            'order_product_color' => $prodColor,
            'order_product_size' => $prodSize,
            'order_quantity' => $quantity,
            'order_cost' => $orderPurchasePrice,
            'order_purchase_price' => $orderCost,
            'order_note' => 'NA',
            'purchase_id' => $prodPur,
            'onway_vndr_prchs_ids' => 'NA',
            'onway_cstmr_prchs_ids' => 'NA',
            'given_by_invntry' => $givenByInventory,
            'order_wear_date' => $wearDateNow,
            'sub_products' => $sub_products,
            'user_flag' => 'customer',
            'order_GUID' => rand(10000, 99999) . date('YmdHis')
        ]);
    }

    private function createOrderWithOnWay(
        $customer_ID,
        $customerName,
        $vendorID,
        $vendorName,
        $productID,
        $prodStyle,
        $prodColor,
        $prodSize,
        $quantity,
        $prodPur,
        $wearDateNow,
        $productInfo,
        $givenByOnWay,
        $orderNow,
        $sub_products
    ) {
        $orderCost = $prodSize < 18
            ? $quantity * $productInfo->product_wholesale_price
            : $quantity * ($productInfo->product_wholesale_price + 30);

        $orderPurchasePrice = $quantity * $productInfo->product_cost;

        return Order::create([
            'order_customer_ID' => $customer_ID,
            'order_customer_name' => $customerName,
            'order_vendor_ID' => $vendorID,
            'order_vendor_name' => $vendorName,
            'order_product_ID' => $productID,
            'order_product_style' => $prodStyle,
            'order_product_color' => $prodColor,
            'order_product_size' => $prodSize,
            'order_quantity' => $quantity,
            'order_cost' => $orderPurchasePrice,
            'order_purchase_price' => $orderCost,
            'order_note' => 'NA',
            'purchase_id' => $prodPur,
            'given_by_onway' => $givenByOnWay,
            'onway_vndr_prchs_ids' => $orderNow->vendor_purchase_ID,
            'onway_cstmr_prchs_ids' => $orderNow->purchase_id,
            'order_wear_date' => $wearDateNow,
            'sub_products' => $sub_products,
            'user_flag' => 'customer',
            'order_GUID' => rand(10000, 99999) . date('YmdHis')
        ]);
    }

    public function popup(Request $request)
    {
        $id = $request->id;
    
        $ownerComp = Customer::where('cust_owner', 'Yes')
            ->value('cust_comp_name');
    
        // Product meta
        $productMeta = Product::where('product_style', $id)
            ->selectRaw('
                MAX(product_status) as product_status,
                MAX(show_from_inventory) as show_from_inventory
            ')
            ->first();
    
        $productStatus = $productMeta->product_status ?? 0;
        $showFromInventory = $productMeta->show_from_inventory ?? 0;
    
        // ✅ FIXED ON WAY (GROUPED)
        $onWay = DB::table('dt_order_allocation')
            ->select(
                'order_product_style',
                'order_product_color',
                'order_product_size',
                DB::raw('SUM(order_quantity) as order_quantity')
            )
            ->where('order_product_style', $id)
            ->where('order_customer_name', $ownerComp)
            ->where('order_quantity', '>', 0)
            ->groupBy('order_product_style', 'order_product_color', 'order_product_size')
            ->get();
    
        // ============================================
        // CASE 1: Inventory Mode
        // ============================================
        if ($productStatus == 0 && $showFromInventory == 1) {
    
            $stock = DB::table('dt_inventory')
                ->select(
                    'product_style',
                    'product_color',
                    'product_size',
                    DB::raw('SUM(product_quantity) as final_quantity')
                )
                ->where('product_style', $id)
                ->groupBy('product_style', 'product_color', 'product_size')
                ->havingRaw('SUM(product_quantity) > 0')
                ->get();
    
            $sizes  = $stock->pluck('product_size')->unique()->values();
            $colors = $stock->pluck('product_color')->unique()->values();
    
            return response()->json([
                'mode'     => 'inventory',
                'products' => [],
                'stock'    => $stock,
                'on_way'   => $onWay,
                'sizes'    => $sizes,
                'colors'   => $colors,
            ]);
        }
    
        // ============================================
        // CASE 2: Active Product
        // ============================================
        $products = Product::where('product_style', $id)
            ->select('product_ID', 'product_style', 'product_color', 'product_size_range')
            ->get()
            ->map(fn($item) => [
                $item->product_ID,
                $item->product_style,
                $item->product_color,
                $item->product_size_range
            ])
            ->toArray();
    
        $stock = DB::table('dt_inventory')
            ->select(
                'product_style',
                'product_color',
                'product_size',
                DB::raw('SUM(product_quantity) as final_quantity')
            )
            ->where('product_style', $id)
            ->groupBy('product_style', 'product_color', 'product_size')
            ->havingRaw('SUM(product_quantity) > 0') // ✅ FIXED
            ->get();
    
        return response()->json([
            'mode'     => 'normal',
            'products' => $products,
            'stock'    => $stock,
            'on_way'   => $onWay,
            'sizes'    => [],
            'colors'   => [],
        ]);
    }
}
