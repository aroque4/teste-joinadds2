<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\PrebidVersion;
use Defender;

class PrebidVersionController extends StandardController {

   protected $nameView = 'prebid-version';
   protected $diretorioPrincipal = 'painel';
   protected $primaryKey = 'id_prebid_version';

   public function __construct(Request $request, PrebidVersion $model, Factory $validator) {
      $this->request = $request;
      $this->model = $model;
      $this->validator = $validator;
   }

   public function getEnableVersion($id){
     $prebids = PrebidVersion::get();
     foreach ($prebids as $prebid) {
       $prebid->update(['enabled' => 0]);
     }
     PrebidVersion::find($id)->update(['enabled' => 1]);
     return back();
   }

}
