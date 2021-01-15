<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Department extends Model {

    public $rules = [
      'name' => 'required'
    ];

    protected $fillable = ['name'];
    protected $primaryKey = 'id_department';
    protected $guarded = ['id_department'];
    protected $table = 'department';

}
