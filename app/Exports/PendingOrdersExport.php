<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendingOrdersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Order::where('order_status', 'Pending')
            ->get()
            ->map(function ($order) {
                return [
                    $order->order_ID,
                    $order->order_customer_ID,
                    strtoupper($order->order_customer_name),
                    $order->order_vendor_ID,
                    strtoupper($order->order_vendor_name),
                    $order->order_product_ID,
                    strtoupper($order->order_product_style),
                    strtoupper($order->order_product_color),
                    $order->order_product_size,
                    $order->order_quantity,
                    $order->given_by_invntry,
                    $order->given_by_onway,
                    $order->order_cost,
                    $order->order_purchase_price,
                    $order->order_note,
                    $order->purchase_id,
                    $order->created_at,
                    strtoupper($order->onway_vndr_prchs_ids),
                    $order->onway_cstmr_prchs_ids,
                    $order->order_status
                ];
            });
    }

    public function headings(): array
    {
        return [
            'order_ID',
            'order_customer_ID',
            'order_customer_name',
            'order_vendor_ID',
            'order_vendor_name',
            'order_product_ID',
            'order_product_style',
            'order_product_color',
            'order_product_size',
            'order_quantity',
            'given_by_invntry',
            'given_by_onway',
            'order_cost',
            'order_purchase_price',
            'order_note',
            'purchase_id',
            'created_at',
            'onway_vndr_prchs_ids',
            'onway_cstmr_prchs_ids',
            'order_status'
        ];
    }
}