<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emailonstatus extends Model
{
    protected $table = 'email_on_status';
    use HasFactory;
    protected $fillable = [
        'suborder_id',
        'box_del_emp',
        'box_del_emp_dt',
        'device_del_start',
        'device_del_start_dt',
        'device_del_comp',
        'device_del_comp_dt',
    ];
}
