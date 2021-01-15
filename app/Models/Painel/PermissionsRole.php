<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class PermissionsRole extends Model {

    public $rules = [

    ];

    protected $fillable = ['permission_id','role_id'];
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $table = 'permission_role';

}
