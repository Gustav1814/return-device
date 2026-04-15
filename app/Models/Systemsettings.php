<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Systemsettings extends Model
{
    protected $table = 'systemsettings';
    use HasFactory;
    protected $fillable = [
        'order_amount',
        'company_id',
        'equipment_type',
    ];
}
