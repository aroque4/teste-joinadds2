<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\FinCurrency;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class FinCurrencyController extends StandardController {

  protected $nameView = 'fin-currency';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_fin_currency';

  public function __construct(Request $request, FinCurrency $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
}
