<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\FinForm;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class FinFormController extends StandardController {

  protected $nameView = 'fin-form';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_fin_form';

  public function __construct(Request $request, FinForm $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
}
