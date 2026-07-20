<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductArchive extends Model
{
    protected $table = 'dt_product_archive';
    protected $primaryKey = 'product_ID';
    public $timestamps = false;
     protected $casts = [
        'sub_products' => 'array',
    ];


    protected $fillable = [
        'product_ID',
        'product_style',
        'factory_style',
        'product_color',
        'product_size_range',
        'product_cost',
        'product_wholesale_price',
        'product_vendor_ID',
        'product_vendor_name',
        'product_link',
        'product_image',
        'product_status',
        'sub_products',
        'archive_name',
    ];
}
