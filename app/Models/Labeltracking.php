<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labeltracking extends Model
{
    protected $table = 'labeltracking';
    use HasFactory;
    protected $fillable = [
        'suborder_id',
        'tracking_id',
        'flag' ,
        'response_date',
        'status',
        'response',
    ];
}
