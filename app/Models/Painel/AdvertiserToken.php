<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AdvertiserToken extends Model {

    public $rules = [
    ];

    protected $fillable = ['token','expire'];
    protected $primaryKey = 'id_advertiser_token';
    protected $guarded = ['id_advertiser_token'];
    protected $table = 'advertiser_token';

}
