<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\DomainCategory;
use Illuminate\Support\Facades\Auth;
use Defender;

class DomainCategoryController extends StandardController {

  protected $nameView = 'domain-category';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_domain_category';

  public function __construct(Request $request, DomainCategory $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
}
