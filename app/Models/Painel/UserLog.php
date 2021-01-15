<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model {

    public $rules = [
    ];

    protected $primaryKey = 'id_user_log';
    protected $guarded = ['id_user_log'];
    protected $table = 'user_log';

}
