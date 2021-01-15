<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\PrebidBids;
use Defender;

class PrebidBidsController extends StandardController {

   protected $nameView = 'prebid-bids';
   protected $diretorioPrincipal = 'painel';
   protected $primaryKey = 'id_prebid_bids';

   public function __construct(Request $request, PrebidBids $model, Factory $validator) {
      $this->request = $request;
      $this->model = $model;
      $this->validator = $validator;
   }

   public function getEnableDisable($idBid){
     $bid = $this->model->find($idBid);

     if($bid->enable == 1){
       $bid->update(['enable' => 0]);
     }else{
       $bid->update(['enable' => 1]);
     }

     return redirect("{$this->diretorioPrincipal}/{$this->nameView}");
   }

}
