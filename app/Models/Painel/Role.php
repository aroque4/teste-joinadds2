<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

    public $rules = [
        'name' => 'required|min:3|max:20'
    ];

    protected $fillable = ['name'];
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $table = 'roles';

}
