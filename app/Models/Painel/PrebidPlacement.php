<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class PrebidPlacement extends Model {

    public $rules = [
      'slot_sizes' => 'required'
    ];

    protected $fillable = ['placement','slot_sizes','pageId','publisherId','placementId','zoneId','region','id_prebid_bids'];
    protected $primaryKey = 'id_prebid_placement';
    protected $guarded = ['id_prebid_placement'];
    protected $table = 'prebid_placement';

}
