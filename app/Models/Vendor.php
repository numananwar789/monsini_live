<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'dt_vendor';
    protected $primaryKey = 'vendor_ID';
    public $timestamps = false;

    protected $fillable = [
        'vendor_name', 'vendor_comp_name', 'vendor_address',
        'vendor_phone', 'vendor_email', 'vendor_fax', 'vendor_agent',
        'message', 'vendor_days', 'vendor_days_stock'
    ];
}

