<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AdUnitRoot extends Model {

    public $rules = [
    ];

    protected $fillable = ['ad_unit_root_id','ad_unit_root_code','ad_unit_root_name','id_domain'];
    protected $primaryKey = 'id_ad_unit_root';
    protected $guarded = ['id_ad_unit_root'];
    protected $table = 'ad_unit_root';

}
