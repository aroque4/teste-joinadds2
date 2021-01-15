<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model {

    public $rules = [
      'name' => 'required'
    ];

    protected $fillable = ['name','earnings'];
    protected $primaryKey = 'id_priority';
    protected $guarded = ['id_priority'];
    protected $table = 'priority';

}
