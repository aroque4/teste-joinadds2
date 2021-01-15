<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {

    public $rules = [
      'subject' => 'required',
      'description' => 'required',
      'id_domain' => 'required'
    ];

    protected $fillable = ['subject','description','id_department','id_ticket_status','id_domain','id_priority','email_ticket','id_user'];
    protected $primaryKey = 'id_ticket';
    protected $guarded = ['id_ticket'];
    protected $table = 'ticket';
}
