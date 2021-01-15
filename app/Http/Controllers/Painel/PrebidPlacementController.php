<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\PrebidPlacement;
use Defender;

class PrebidPlacementController extends StandardController {

   protected $nameView = 'prebid-placement';
   protected $diretorioPrincipal = 'painel';
   protected $primaryKey = 'id_prebid_placement';

   public function __construct(Request $request, PrebidPlacement $model, Factory $validator) {
      $this->request = $request;
      $this->model = $model;
      $this->validator = $validator;
   }

   public function getSessionBids($idBid){
     session(['id_prebid_bids' => $idBid]);
     return redirect("{$this->diretorioPrincipal}/$this->nameView");
   }


   public function getIndex() {
     if (Defender::hasPermission("prebid-bids")) {
       $data = $this->model->where('id_prebid_bids', session('id_prebid_bids'))->paginate($this->totalItensPorPagina);
       $principal = $this->diretorioPrincipal;
       $primaryKey = $this->primaryKey;
       $rota = $this->nameView;
       return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
     } else {
       return redirect("/{$this->diretorioPrincipal}");
     }
   }

   public function getCreate() {
     if (Defender::hasPermission("prebid-bids")) {
       $principal = $this->diretorioPrincipal;
       $rota = $this->nameView;
       $primaryKey = $this->primaryKey;
       return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey'));
     } else {
       return redirect("/{$this->diretorioPrincipal}");
     }
   }

   public function postStore() {
     if (Defender::hasPermission("prebid-bids")) {
       $dadosForm = $this->request->all();
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

   public function getShow($id) {
     if (Defender::hasPermission("prebid-bids")) {
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
     if (Defender::hasPermission("prebid-bids")) {
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

   public function postDestroy($id) {
     if (Defender::hasPermission("prebid-bids")) {
       $this->model->findOrFail($id)->delete();
       return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
     } else {
       return redirect("/{$this->diretorioPrincipal}");
     }
   }

}
