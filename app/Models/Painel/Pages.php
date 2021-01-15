<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Pages extends Model {

    public $rules = [
      'icon' => 'required',
      'name' => 'required',
      'image' => 'max:512',
      'position' => 'required'
    ];

    protected $fillable = ['icon','name','image','position','url','new','link','open_page', 'type'];
    protected $primaryKey = 'id_pages';
    protected $guarded = ['id_pages'];
    protected $table = 'pages';

}
