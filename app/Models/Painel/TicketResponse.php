<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class TicketResponse extends Model {

    public $rules = [
      'response' => 'required'
    ];

    protected $fillable = ['response','id_ticket','type','id_user'];
    protected $primaryKey = 'id_ticket_response';
    protected $guarded = ['id_ticket_response'];
    protected $table = 'ticket_response';

}
