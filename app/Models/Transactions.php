<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'company_id',
        'user_id' ,
        'trans_response', 
        'status',
        'amount',
    ];
}
