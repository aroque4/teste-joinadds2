<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\AssignmentStatus;
use Illuminate\Support\Facades\Auth;
use Defender;
use DB;

class AssignmentStatusController extends StandardController {

  protected $nameView = 'assignment-status';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_assignment_status';

  public function __construct(Request $request, AssignmentStatus $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

}
