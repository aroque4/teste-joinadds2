<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinPurchases extends Model {

    public $rules = [
      'name' => 'required',
      'valor' => 'required',
    ];

    protected $fillable = ['name','valor','observation','status','id_user','id_user_approval'];
    protected $primaryKey = 'id_fin_purchases';
    protected $guarded = ['id_fin_purchases'];
    protected $table = 'fin_purchases';

}
