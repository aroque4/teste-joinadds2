<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Article extends Model {

    public $rules = [
      'subject' => 'required',
      'description' => 'required'
    ];

    protected $fillable = ['subject','description'];
    protected $primaryKey = 'id_article';
    protected $guarded = ['id_article'];
    protected $table = 'article';

}
