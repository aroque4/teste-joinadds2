<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AssignmentServiceHour extends Model {

    public $rules = [
    ];

    protected $primaryKey = 'id_assignment_service_hour';
    protected $guarded = ['id_assignment_service_hour'];
    protected $table = 'assignment_service_hour';

}
