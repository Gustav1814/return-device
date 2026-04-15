<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Companies;
use App\Models\Compemployees;
class Orders extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'status'      
    ];

    public function companies()
    {
        return $this->belongsTo(Companies::class);
    }

    public function compemployees()
    {
        return $this->hasMany(Compemployees::class,'order_id','id');
    }
}
