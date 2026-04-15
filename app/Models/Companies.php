<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Orders;
class Companies extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'receipient_name',
        'company_email',
        'company_add_1',
        'company_add_2',
        'company_phone',
        'company_city',
        'company_state',
        'company_zip',
        'equipment_type',
        'service_type',
        'company_domain',
        'parent_company',
        'domain'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Orders::class);
    }

}
