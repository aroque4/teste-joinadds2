<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\TicketStatus;
use Illuminate\Support\Facades\Auth;
use Defender;

class TicketStatusController extends StandardController {

  protected $nameView = 'ticket-status';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_ticket_status';

  public function __construct(Request $request, TicketStatus $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
}
