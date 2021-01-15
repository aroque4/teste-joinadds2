<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinMassPaymentItem extends Model {

    public $rules = [
      // 'id_husk' => 'required',
    ];

    protected $fillable = ['token','final_value','addids','obs','id_user','id_client','id_fin_mass_payment','id_fin_movimentation','id_fin_currency'];
    protected $primaryKey = 'id_fin_mass_payment_item';
    protected $guarded = ['id_fin_mass_payment_item'];
    protected $table = 'fin_mass_payment_item';

}
