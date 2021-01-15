<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainChecklist extends Model {

    public $rules = [
      'name' => 'required'
    ];

    protected $fillable = ['name','ordem','status'];
    protected $primaryKey = 'id_domain_checklist';
    protected $guarded = ['id_domain_checklist'];
    protected $table = 'domain_checklist';

}
