<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model {

    public $rules = [
      'subject' => 'required',
      'description' => 'required',
      'start_date' => 'required',
      'end_date' => 'required',
      'id_priority' => 'required',
    ];

    protected $fillable = ['subject','description','start_date','id_ticket','end_date','id_priority','id_assignment_status','id_domain', 'id_client','id_department'];
    protected $primaryKey = 'id_assignment';
    protected $guarded = ['id_assignment'];
    protected $table = 'assignment';

}
