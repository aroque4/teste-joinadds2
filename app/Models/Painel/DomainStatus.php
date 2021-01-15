<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainStatus extends Model {

    public $rules = [
      'name' => 'required'
    ];

    protected $fillable = ['name'];
    protected $primaryKey = 'id_domain_status';
    protected $guarded = ['id_domain_status'];
    protected $table = 'domain_status';

}
