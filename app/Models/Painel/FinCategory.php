<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinCategory extends Model {

    public $rules = [
      'name' => 'required',
      'status' => 'required',
    ];

    protected $fillable = ['name','status','fin_category_id'];
    protected $primaryKey = 'id_fin_category';
    protected $guarded = ['id_fin_category'];
    protected $table = 'fin_category';

}
