<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companysettings extends Model
{
    protected $table = 'companysettings';

    protected $fillable = [
        'company_id',
        'logo',
        'btn_bg_color',
        'btn_font_color',
        'theme_bg_color',
        'theme_font_color',
        'settings_data',
        'favicon'
    ];

    protected $casts = [
        'settings_data' => 'array',
    ];

    public function company(){
        return $this->belongsTo(Companies::class);
    }
}
