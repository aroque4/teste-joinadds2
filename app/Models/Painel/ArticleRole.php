<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class ArticleRole extends Model {

    public $rules = [
    ];

    protected $fillable = ['id_role','id_article'];
    protected $primaryKey = 'id_article_role';
    protected $guarded = ['id_article_role'];
    protected $table = 'article_role';

}
