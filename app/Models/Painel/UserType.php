<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserType extends Model {
  use SoftDeletes;

    public $rules = [
      'name' => 'required'
    ];

    protected $fillable = ['name'];
    protected $primaryKey = 'id_user_type';
    protected $guarded = ['id_user_type'];
    protected $table = 'user_type';
    protected $dates = ['deleted_at'];


}
