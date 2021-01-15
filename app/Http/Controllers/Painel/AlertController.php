<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Alert;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class AlertController extends StandardController {

  protected $nameView = 'alert';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_alert';

  public function __construct(Request $request, Alert $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function postStore() {
    
    $dadosForm = $this->request->all();
    $validator = $this->validator->make($dadosForm, $this->model->rules);
    if ($validator->fails()) {
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
    }
    $this->model->create($dadosForm);
    return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
  
}
}
