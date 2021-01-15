<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AdUnitBid extends Model {

    public $rules = [
    ];

    protected $fillable = ['id_ad_unit','id_prebid_bids'];
    protected $primaryKey = 'id_ad_unit_bid';
    protected $guarded = ['id_ad_unit_bid'];
    protected $table = 'ad_unit_bid';

}
