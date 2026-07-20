<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'dt_inventory';
    protected $primaryKey = 'uID';
    public $timestamps = false;

    protected $fillable = [
        'product_ID', 'product_style', 'product_color', 'product_size',
        'product_cost', 'product_wholesale_price', 'product_vendor_ID',
        'product_vendor_name', 'product_link', 'product_image', 'product_quantity'
    ];
}
