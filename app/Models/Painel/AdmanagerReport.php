<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AdmanagerReport extends Model {

    public $rules = [
    ];

    protected $primaryKey = 'id_admanager_report';
    protected $guarded = ['id_admanager_report'];
    protected $table = 'admanager_report';

}
