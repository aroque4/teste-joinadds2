<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class PermissionsUser extends Model {

    public $rules = [

    ];

    protected $fillable = ['users_id','roles_id'];
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $table = 'permission_user';

}
