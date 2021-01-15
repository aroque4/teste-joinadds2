<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Settings extends Model {
    use SoftDeletes;

    public $rules = [
      'name_system' => 'required'
    ];

    protected $fillable = ['name_system','logo_white','logo_black','fiv_icon','backgroud_login','email_ticket','cpm','dolar'];
    protected $primaryKey = 'id_settings';
    protected $guarded = ['id_settings'];
    protected $table = 'settings';
    protected $dates = ['deleted_at'];

}
