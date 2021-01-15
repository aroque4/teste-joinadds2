<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainScripts extends Model {

    public $rules = [
    ];

    protected $fillable = ['header','footer','after_body','device','id_domain'];
    protected $primaryKey = 'id_domain_scripts';
    protected $guarded = ['id_domain_scripts'];
    protected $table = 'domain_scripts';
}
