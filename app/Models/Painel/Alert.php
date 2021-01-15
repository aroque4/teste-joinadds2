<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model {

    public $rules = [
      // 'image' => 'max:512'
      'title'=>'required',
      'text'=>'required',
    ];

    protected $fillable = ['title','text','status','id_user'];
    protected $primaryKey = 'id_alert';
    protected $guarded = ['id_alert'];
    protected $table = 'alert';

}
