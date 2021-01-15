<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Permissions;
use Defender;

class PermissionsController extends StandardController {

  protected $nameView = 'permissions';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id';

  public function __construct(Request $request, Permissions $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getCreate() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $Permissoes = $this->model->where('name', NULL)->get();

      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey', 'Permissoes'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getShow($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      $PermissaoSelecionada = $this->model->find($data->id_permission);
      $Permissoes = $this->model->where('name', NULL)->get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data', 'principal', 'rota', 'primaryKey', 'Permissoes','PermissaoSelecionada'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }




}
