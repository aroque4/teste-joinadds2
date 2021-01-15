<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class ToolsController extends StandardController {

  protected $nameView = 'tools';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id';

  public function __construct(Request $request, Factory $validator) {
    $this->request = $request;
    // $this->model = $model;
    $this->validator = $validator;
  }

  public function getUrlBuilder() {

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.url-builder", compact('principal', 'rota', 'primaryKey'));

  }

}