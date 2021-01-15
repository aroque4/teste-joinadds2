<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Modal extends Model {

    public $rules = [
      // 'image' => 'max:512'
    ];

    protected $fillable = ['image','status'];
    protected $primaryKey = 'id_modal';
    protected $guarded = ['id_modal'];
    protected $table = 'modal';

}
