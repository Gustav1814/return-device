<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'email_flag',
        'sms_flag' ,
        'sms_phone',    
    ];
}
