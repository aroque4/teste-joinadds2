<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model {

    public $rules = [
      'title' => 'required',
      'description' => 'required'
    ];

    protected $fillable = ['title','description','status'];
    protected $primaryKey = 'id_contract';
    protected $guarded = ['id_contract'];
    protected $table = 'contract';

}
