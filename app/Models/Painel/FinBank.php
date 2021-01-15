<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinBank extends Model {

    public $rules = [
      'name' => 'required',
      'valor' => 'required',
    ];

    protected $fillable = ['name','valor','id_fin_currency','status'];
    protected $primaryKey = 'id_fin_bank';
    protected $guarded = ['id_fin_bank'];
    protected $table = 'fin_bank';

}
