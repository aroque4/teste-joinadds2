<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\AdsTxtDefault;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class AdsTxtDefaultController extends StandardController {

  protected $nameView = 'ads-txt-default';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_ads_txt_default';

  public function __construct(Request $request, AdsTxtDefault $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
}
