<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinForm extends Model {

    public $rules = [
      'name' => 'required',
      'status' => 'required',
    ];

    protected $fillable = ['name','status'];
    protected $primaryKey = 'id_fin_form';
    protected $guarded = ['id_fin_form'];
    protected $table = 'fin_form';

}
