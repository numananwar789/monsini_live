<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OrderCancel;
use App\Models\OrderFinalCancel;
use App\Models\OrderAllocationCancel;

class OrderCancelController extends Controller
{
   public function index()
    {

        
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }
        
        $ownerComp = DB::table('dt_cust')
            ->where('cust_owner', 'Yes')
            ->value('cust_comp_name');

        $archiveListNew = OrderCancel::all();
        $archiveListNew_final = OrderFinalCancel::all();
        $archiveListNew_allocation = OrderAllocationCancel::all();

        return view('cancelled-orders.index', compact(
            'ownerComp',
            'archiveListNew',
            'archiveListNew_final',
            'archiveListNew_allocation'
        ));
    }

    public function restore(Request $request)
    {
        if ($request->has('history')) {
            $productList = $request->input('history');
            
            // Restore from order_cancel table
            $countFromCancelTable = OrderCancel::whereIn('order_ID', $productList)->count();
            if ($countFromCancelTable > 0) {
                DB::statement("INSERT INTO dt_order SELECT * FROM dt_order_cancel WHERE order_ID IN (".implode(',', array_fill(0, count($productList), '?')).")", $productList);
                OrderCancel::whereIn('order_ID', $productList)->delete();
            }

            // Restore from order_final_cancel table
            $countFromFinalCancelTable = OrderFinalCancel::whereIn('order_ID', $productList)->count();
            if ($countFromFinalCancelTable > 0) {
                DB::statement("INSERT INTO dt_order_final SELECT * FROM dt_order_final_cancel WHERE order_ID IN (".implode(',', array_fill(0, count($productList), '?')).")", $productList);
                OrderFinalCancel::whereIn('order_ID', $productList)->delete();
            }

            // Restore from order_allocation_cancel table
            $countFromAllocationCancelTable = OrderAllocationCancel::whereIn('order_ID', $productList)->count();
            if ($countFromAllocationCancelTable > 0) {
                DB::statement("INSERT INTO dt_order_allocation SELECT * FROM dt_order_allocation_cancel WHERE order_ID IN (".implode(',', array_fill(0, count($productList), '?')).")", $productList);
                OrderAllocationCancel::whereIn('order_ID', $productList)->delete();
            }
        } else {
            // Restore all records
            DB::statement("INSERT INTO dt_order SELECT * FROM dt_order_cancel");
            DB::table('dt_order_cancel')->truncate();

            DB::statement("INSERT INTO dt_order_final SELECT * FROM dt_order_final_cancel");
            DB::table('dt_order_final_cancel')->truncate();

            DB::statement("INSERT INTO dt_order_allocation SELECT * FROM dt_order_allocation_cancel");
            DB::table('dt_order_allocation_cancel')->truncate();
        }

        return redirect()->route('cancelled-orders');
    }
}
