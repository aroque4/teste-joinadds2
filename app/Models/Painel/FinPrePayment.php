<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinPrePayment extends Model {

    public $rules = [
    //   'id_husk' => 'required',
    ];

    protected $fillable = ['id_user','id_client','id_fin_currency','date','addids'];
    protected $primaryKey = 'id_fin_pre_payment';
    protected $guarded = ['id_fin_pre_payment'];
    protected $table = 'fin_pre_payment';

}
