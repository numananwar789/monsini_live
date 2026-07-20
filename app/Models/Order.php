<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'dt_order';
    protected $primaryKey = 'order_ID';
    public $timestamps = false;

    protected $casts = [
        'sub_products' => 'array',
    ];

    protected $fillable = [
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
        'order_status',
        'order_wear_date',
        'user_flag',
        'sub_products',
        'order_GUID'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'order_customer_ID', 'cust_ID');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'order_vendor_ID', 'vendor_ID');
    }
}
