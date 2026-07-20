<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailBody extends Model
{
    protected $table = 'email_body';
    protected $primaryKey = 'email_id';
    public $timestamps = false;

    protected $fillable = [
        'email_body', 'email_role'
    ];

    public static function getTemplate($role)
    {
        return self::firstOrCreate(
            ['email_role' => $role],
            ['email_body' => "Default $role email template"]
        );
    }
}

