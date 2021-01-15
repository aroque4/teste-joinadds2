<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainFixed extends Model {

    protected $fillable = ['id_domain','type','status'];
    protected $primaryKey = 'id_domain_fixed';
    protected $guarded = ['id_domain_fixed'];
    protected $table = 'domain_fixed';

}
