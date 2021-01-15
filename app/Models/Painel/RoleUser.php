<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model {

    public $rules = [

    ];

    public $timestamps = false;
    
    protected $fillable = ['user_id','role_id'];
    protected $primaryKey = 'id_role_user';
    protected $guarded = ['id_role_user'];
    protected $table = 'role_user';

}
