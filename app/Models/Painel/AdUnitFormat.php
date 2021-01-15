<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AdUnitFormat extends Model {

    public $rules = [
      'name' => 'required',
      'page' => 'required',
      'quantity' => 'required',
      'sizes' => 'required',
      'device' => 'required',
    ];

    protected $fillable = ['name','page','quantity','sizes','device','position'];
    protected $primaryKey = 'id_ad_unit_format';
    protected $guarded = ['id_ad_unit_format'];
    protected $table = 'ad_unit_format';

}
