<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\FinPurchases;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;
use Helper;

class FinPurchasesController extends StandardController {

  protected $nameView = 'fin-purchases';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_fin_purchases';

  public function __construct(Request $request, FinPurchases $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getIndex() {
    
      $data = $this->model
                   ->selectRaw('fin_purchases.id_fin_purchases, fin_purchases.name, fin_purchases.observation, fin_purchases.status, User.name as solicitante, Adm.name as approval, fin_purchases.created_at as date, valor')
                   ->join('users as User','User.id','fin_purchases.id_user')
                   ->leftJoin('users as Adm','Adm.id','id_user_approval')
                   ->get();
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));

  }

  public function getCreate() {
    
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey'));
    
  }

  public function postStore() {
    
      $dadosForm = $this->request->all();
      $dadosForm['valor'] = Helper::moedaSys($dadosForm['valor']);
      $dadosForm['id_user'] = Auth::user()->id;
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $this->model->create($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    
  }

  public function getShow($id) {
    
      $data = $this->model->findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data', 'principal', 'rota', 'primaryKey'));

  }

  public function postUpdate($id) {

      $dadosForm = $this->request->all();;
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      $dadosForm['valor'] = Helper::moedaSys($dadosForm['valor']);
      
      if($dadosForm['status'] == 1 || $dadosForm['status'] == 2){
        $dadosForm['id_user_approval'] = Auth::user()->id;
      }
      
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
      }
      $this->model->findOrFail($id)->update($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
  }

  public function postDestroy($id) {
    
      $this->model->findOrFail($id)->delete();
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    
  }

}
