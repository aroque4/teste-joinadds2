<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinCurrency extends Model {

    public $rules = [
    //   'id_husk' => 'required',
    ];

    protected $fillable = ['name','abbreviation','status'];
    protected $primaryKey = 'id_fin_currency';
    protected $guarded = ['id_fin_currency'];
    protected $table = 'fin_currency';

}
