<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class PrebidBids extends Model {

    public $rules = [
      'name' => 'required',
      'bidder' => 'required'
    ];

    protected $fillable = ['name','enable','bidder','network','bid_floor','reserve'];
    protected $primaryKey = 'id_prebid_bids';
    protected $guarded = ['id_prebid_bids'];
    protected $table = 'prebid_bids';

}
