<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinMassPayment extends Model {

    public $rules = [
      'id_husky' => 'required',
    ];

    protected $fillable = ['id_user','status','id_husky'];
    protected $primaryKey = 'id_fin_mass_payment';
    protected $guarded = ['id_fin_mass_payment'];
    protected $table = 'fin_mass_payment';

}
