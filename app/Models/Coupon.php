<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupon';
    use HasFactory;

    protected $fillable = [
        'coupon',
        'type',
        'coupon_apply_for',
        'amt_or_perc',
        'status',
        'freeall'
 
    ];
    
}
