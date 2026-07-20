<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAllocation extends Model
{
    protected $table = 'dt_order_allocation';
    protected $primaryKey = 'allocation_ID';
    public $timestamps = false;

    protected $casts = [
        'sub_products' => 'array',
    ];

    protected $fillable = [
        'final_ID',
        'order_ID',
        'order_customer_ID',
        'order_customer_name',
        'order_vendor_ID',
        'order_vendor_name',
        'vendor_purchase_ID',
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
        'created_at_final',
        'created_at_allocation',
        'onway_vndr_prchs_ids',
        'onway_cstmr_prchs_ids',
        'order_status',
        'bypass',
        'order_wear_date',
        'user_flag',
        'order_GUID',
        'staging_flag',
        'staging_date',
        'sub_products'
    ];
}
