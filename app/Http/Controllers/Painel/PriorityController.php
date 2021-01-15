<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Priority;
use Illuminate\Support\Facades\Auth;
use Defender;

class PriorityController extends StandardController {

  protected $nameView = 'priority';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_priority';

  public function __construct(Request $request, Priority $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

}
