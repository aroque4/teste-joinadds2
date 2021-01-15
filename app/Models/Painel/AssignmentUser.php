<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AssignmentUser extends Model {

    public $rules = [

    ];

    protected $fillable = ['id_assignment','id_user'];
    protected $primaryKey = 'id_assignment_user';
    protected $guarded = ['id_assignment_user'];
    protected $table = 'assignment_user';

}
