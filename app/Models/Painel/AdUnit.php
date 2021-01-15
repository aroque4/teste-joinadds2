<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AdUnit extends Model {

    public $rules = [
    ];

    protected $fillable = ['ad_unit_id','lazyload','refresh','id_div','ad_unit_name','ad_unit_code','ad_unit_status','position','device','sizes','shortcode','element_html','position_element','id_ad_unit_root'];
    protected $primaryKey = 'id_ad_unit';
    protected $guarded = ['id_ad_unit'];
    protected $table = 'ad_unit';

}
