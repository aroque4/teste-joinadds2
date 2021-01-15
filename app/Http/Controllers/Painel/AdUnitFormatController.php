<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\AdUnitFormat;
use Illuminate\Support\Facades\Auth;
use Defender;

class AdUnitFormatController extends StandardController {

  protected $nameView = 'ad-unit-format';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_ad_unit_format';

  public function __construct(Request $request, AdUnitFormat $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getShow($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      foreach(explode(',',$data->sizes) as $size){
        $sizes[] = $size;
      }

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data','sizes', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postStore() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();

      $dadosForm['sizes'] = implode($dadosForm['sizes'], ',');

      $validator = $this->validator->make($dadosForm, $this->model->rules);
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

      $dadosForm['sizes'] = implode($dadosForm['sizes'], ',');

      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
      }
      $this->model->findOrFail($id)->update($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

}
