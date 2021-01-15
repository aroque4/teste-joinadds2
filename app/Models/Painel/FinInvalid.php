<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinInvalid extends Model {
  use SoftDeletes;

    public $rules = [
      'month' => 'required',
      'year' => 'required',
      'id_client' => 'required',
      'id_user' => 'required',
      'value' => 'required',
      'id_domain' => 'required',
    ];

    protected $fillable = ['month','year','id_client','id_user','value','id_domain'];
    protected $primaryKey = 'id_fin_invalid';
    protected $guarded = ['id_fin_invalid'];
    protected $table = 'fin_invalid';
    protected $dates = ['deleted_at'];


}
