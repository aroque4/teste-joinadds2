<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Permissions extends Model {

    public $rules = [
        'readable_name' => 'required|min:3|max:100',
    ];

    protected $fillable = ['name','new','readable_name','icon','id_permission','invisible','order','menu_fix'];
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $table = 'permissions';

}
