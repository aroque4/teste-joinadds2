<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Modal;
use App\Models\Painel\ModalUser;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class ModalController extends StandardController {

  protected $nameView = 'modal';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_modal';

  public function __construct(Request $request, Modal $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function postStore() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();

      $validator = $this->validator->make($dadosForm, $this->model->rules);

      if (!empty($this->request->file('image'))) {
        $dadosForm['image'] = $this->uploadFile($this->request->file('image'), "modal", "painel", "modal");
      }

      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $this->model->create($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postUpdate($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      $Configuracoes = $this->model->findOrFail($id);

      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
      }

      if (!empty($this->request->file('image'))) {
        file::delete('assets/painel/uploads/modal/' . $Configuracoes->image);
        $dadosForm['image'] = $this->uploadFile($this->request->file('image'), "modal", "painel", "modal");
      }

      $this->model->findOrFail($id)->update($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getModalClose($id) {
    $dadosForm['id_modal'] = $id;
    $dadosForm['id_user'] = Auth::user()->id;
    ModalUser::create($dadosForm);
    return 1;
  }


}
