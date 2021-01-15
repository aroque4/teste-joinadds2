<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainCategory extends Model {

    public $rules = [
      'name' => 'required'
    ];

    protected $fillable = ['name'];
    protected $primaryKey = 'id_domain_category';
    protected $guarded = ['id_domain_category'];
    protected $table = 'domain_category';

}
