<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\MessageDefault;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class MessageDefaultController extends StandardController {

  protected $nameView = 'message-default';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_message_default';

  public function __construct(Request $request, MessageDefault $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
}
