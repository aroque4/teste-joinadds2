<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Pages;
use Defender;
use File;

class PagesController extends StandardController {

  protected $nameView = 'pages';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_pages';

  public function __construct(Request $request, Pages $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function postStore() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();

      $validator = $this->validator->make($dadosForm, $this->model->rules);

      $dadosForm['url'] = $this->urlAmigavel($dadosForm['name']);

      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }

      if (!empty($this->request->file('image'))) {
        $dadosForm['image'] = $this->uploadFile($this->request->file('image'), $dadosForm['name'], "painel", "pages");
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

      $dadosForm['url'] = $this->urlAmigavel($dadosForm['name']);

      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
      }

      if (!empty($this->request->file('image'))) {
        file::delete('assets/painel/uploads/pages/' . $Configuracoes->image);
        $dadosForm['image'] = $this->uploadFile($this->request->file('image'), $dadosForm['name'], "painel", "pages");
      }

      $this->model->findOrFail($id)->update($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getOpenPage($url){
    $data = $this->model->where('url', $url)->first();
    $principal = $this->diretorioPrincipal;
    $primaryKey = $this->primaryKey;
    $rota = $this->nameView;
    return view("{$this->diretorioPrincipal}.{$this->nameView}.page", compact('data', 'principal', 'rota', 'primaryKey'));
  }


}
