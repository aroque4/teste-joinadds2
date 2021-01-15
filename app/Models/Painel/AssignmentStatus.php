<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AssignmentStatus extends Model {

    public $rules = [
      'name' => 'required',
      'color' => 'required'
    ];

    protected $fillable = ['name','color'];
    protected $primaryKey = 'id_assignment_status';
    protected $guarded = ['id_assignment_status'];
    protected $table = 'assignment_status';

}
