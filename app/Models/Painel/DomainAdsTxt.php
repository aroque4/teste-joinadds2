<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainAdsTxt extends Model {

    public $rules = [
    ];

    protected $fillable = ['script','id_domain'];
    protected $primaryKey = 'id_domain_ads_txt';
    protected $guarded = ['id_domain_ads_txt'];
    protected $table = 'domain_ads_txt';

}
