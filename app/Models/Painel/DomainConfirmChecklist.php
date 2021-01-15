<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainConfirmChecklist extends Model {

    public $rules = [
    ];

    protected $fillable = ['id_domain','id_domain_checklist',''];
    protected $primaryKey = 'id_domain_confirm_checklist';
    protected $guarded = ['id_domain_confirm_checklist'];
    protected $table = 'domain_confirm_checklist';

}
