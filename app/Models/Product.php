<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'dt_product';
    protected $primaryKey = 'product_ID';
    public $timestamps = false;

    protected $casts = [
        'sub_products' => 'array',
    ];

    protected $fillable = [
        'product_style',
        'product_color',
        'product_size_range',
        'product_cost',
        'product_wholesale_price',
        'product_vendor_ID',
        'product_vendor_name',
        'product_link',
        'product_image',
        'product_status',
        'factory_style',
        'sub_products',
        'version_year'
    ];


    public function scopeActive($query)
    {
        return $query->where('product_status', 1);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'order_product_ID', 'product_ID');
    }
}
