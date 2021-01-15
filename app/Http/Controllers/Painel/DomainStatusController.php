<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\DomainStatus;
use Illuminate\Support\Facades\Auth;
use Defender;

class DomainStatusController extends StandardController {

  protected $nameView = 'domain-status';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_domain_status';

  public function __construct(Request $request, DomainStatus $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
}
