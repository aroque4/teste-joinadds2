<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainChecklistObservation extends Model {

    public $rules = [
    ];

    protected $fillable = ['description','id_domain','id_user'];
    protected $primaryKey = 'id_domain_checklist_observation';
    protected $guarded = ['id_domain_checklist_observation'];
    protected $table = 'domain_checklist_observation';

}
