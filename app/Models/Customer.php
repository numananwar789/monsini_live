<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'dt_cust';
    protected $primaryKey = 'cust_ID';
    public $timestamps = false;

    protected $fillable = [
        'f_name',
        'l_name',
        'cust_username',
        'cust_password',
        'cust_comp_name',
        'cust_address',
        'country',
        'zip',
        'cust_phone',
        'cust_email',
        'cust_fax',
        'cust_sales_rep',
        'cust_status',
        'cust_owner',
        'user_id'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'order_customer_ID', 'cust_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
