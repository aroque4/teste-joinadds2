<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Advertiser extends Model {

    public $rules = [
    ];

    protected $fillable = ['title','description','image','active_view','url','cpc','total','start_date','end_date','type_campaign','advertiser_id_integration','status_ad_manager','order_id','line_item_id','status_approved'];
    protected $primaryKey = 'id_advertiser';
    protected $guarded = ['id_advertiser'];
    protected $table = 'advertiser';

}
