<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\EmailsTemplate;
use Mail;
use DB;

class EmailsTemplateController extends StandardController {

  protected $nameView = 'emails-template';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_emails_template';

  public function __construct(Request $request, EmailsTemplate $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function postSessionHtml(){
    $dadosForm = $this->request->all();
    session(['templateHtml' => $dadosForm['html']]);
    return 1;
  }

  public function getShowHtml(){
    echo session('templateHtml');
  }
}
