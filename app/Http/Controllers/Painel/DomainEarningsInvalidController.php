<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\DomainEarningsInvalid;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class DomainEarningsInvalidController extends StandardController {

   protected $nameView = 'domain-earnings-invalid';
   protected $diretorioPrincipal = 'painel';
   protected $primaryKey = 'id_domain_earnings_invalid';

   public function __construct(Request $request, DomainEarningsInvalid $model, Factory $validator) {
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
       $dadosForm['value'] = $this->formatar_moeda($dadosForm['value']);

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

   public function formatar_moeda($data){
     if($data != null){
         $date = str_replace('.','',$data);
         $newdate = str_replace(',','.',$date);
         return $newdate;
     }
   }

}
