<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Orders;

class Compemployees extends Model
{
    protected $table = 'compemployees';
    use HasFactory;
    protected $fillable = [
        'emp_first_name',
        'emp_last_name',
        'emp_email',
        'emp_phone',
        'emp_add_1',
        'emp_add_2',
        'emp_city',
        'emp_state',
        'emp_pcode',
        'type_of_equip',
        'return_service',
        'company_id',
        'user_id',
        'order_id',
        'receipient_name',
        'receipient_email',
        'receipient_phone',
        'receipient_add_1',
        'receipient_add_2',
        'receipient_city',
        'receipient_state',
        'receipient_zip',
        'send_flag',
        'rec_flag',
        'receipient_person',
        'source',
        'insurance_active',
        'insurance_amount',
        'custom_msg',
        'order_amt',
        'return_additional_srv',
        'new_emp_data',
        'dest_flag',
        'dest_labelresponse',
        'dest_label_status',
        'parent_comp_id'

    ];


    public function order()
    {
        return $this->belongsTo(Orders::class);
    }

}
