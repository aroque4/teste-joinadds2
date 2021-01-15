<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinServices extends Model {

    public $rules = [
      // 'image' => 'max:512'
      'title'=>'required',
      'text'=>'required',
    ];

    protected $fillable = [
        'id_fin_services',
        'day',
        'month',
        'year',
        'value',
        'id_user',
        'id_fin_currency',
        'id_fin_bank',
        'client_id',
    ];
    protected $primaryKey = 'id_fin_services';
    protected $guarded = ['id_fin_services'];
    protected $table = 'fin_services';

}
