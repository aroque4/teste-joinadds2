<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Department;
use Illuminate\Support\Facades\Auth;
use Defender;

class DepartmentController extends StandardController {

  protected $nameView = 'department';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_department';

  public function __construct(Request $request, Department $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
}
