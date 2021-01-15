<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\DomainNotification;
use App\Models\Painel\User;
use Illuminate\Support\Facades\Auth;
use Defender;

class DomainNotificationController extends StandardController {

  protected $nameView = 'domain-notification';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_domain_notification';

  public function __construct(Request $request, DomainNotification $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getSessionDomain($idDomain){
    session(['id_domain' => $idDomain]);
    return redirect("{$this->diretorioPrincipal}/$this->nameView");
  }


  public function getIndex() {
    if (Defender::hasPermission("domain")) {
      $data = $this->model->where('id_domain', session('id_domain'))->paginate($this->totalItensPorPagina);
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getCreate() {
    if (Defender::hasPermission("domain")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postStore() {
    if (Defender::hasPermission("domain")) {
      $dadosForm = $this->request->all();
      $dadosForm['id_domain'] = session('id_domain');
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $this->model->create($dadosForm);

      $user = User::join('domain','domain.id_user', 'users.id')
      ->where('id_domain', session('id_domain'))
      ->first();

      $this->sendEmail('notification',['observation' => $dadosForm['message']],$dadosForm['subject'], $user->email);

      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getStatus($id=null){
    $data = $this->model->where('id_domain_notification',$id)->update(['read'=>1]);
    echo $id;
    die();
  }
}
