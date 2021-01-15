<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model {

    public $rules = [
      'name' => 'required'
    ];

    protected $fillable = ['name'];
    protected $primaryKey = 'id_ticket_status';
    protected $guarded = ['id_ticket_status'];
    protected $table = 'ticket_status';

}
