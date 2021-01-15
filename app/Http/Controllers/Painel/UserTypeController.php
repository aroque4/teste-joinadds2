<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\UserType;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class UserTypeController extends StandardController {

  protected $nameView = 'user-type';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_user_type';

  public function __construct(Request $request, UserType $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

}
