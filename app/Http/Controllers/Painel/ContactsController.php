<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Contacts;
use Illuminate\Support\Facades\Auth;
use App\Models\Painel\User;
use Defender;
use Helper;

class ContactsController extends StandardController {

  protected $nameView = 'contacts';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_contact';

  public function __construct(Request $request, Contacts $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }
  public function getIndex() {
    // if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->paginate(100);
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
    // } else {
    //   return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function getCreate($user_id=null) {
    // if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $user = User::where('id',$user_id)->first();
      $contacts = Contacts::where('id_prospect',$user_id)->get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey','user','contacts'));
    // } else {
    //   return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function postStore() {
    // if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();
      $dadosForm['return_in'] = Helper::formatData($dadosForm['return_in'],1);
      // $validator = $this->validator->make($dadosForm, $this->model->rules);
      
      $this->model->create($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create/".$dadosForm['id_prospect']);
    // } else {
      // return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function getShow($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postUpdate($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();
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
