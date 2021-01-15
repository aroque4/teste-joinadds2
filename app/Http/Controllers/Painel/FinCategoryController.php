<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\FinCategory;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class FinCategoryController extends StandardController {

  protected $nameView = 'fin-category';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_fin_category';

  public function __construct(Request $request, FinCategory $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getIndex() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model
                    ->leftJoin('fin_category as F', 'F.id_fin_category','=','fin_category.fin_category_id')
                    ->selectRaw('fin_category.*, F.name nameCategory')
                    ->paginate($this->totalItensPorPagina);
                    
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getCreate() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $categories = $this->model->where('fin_category_id')->get();

      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey', 'categories'));
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
  
      $categories = $this->model->where('fin_category_id isNull')->get();
      $categoriesSelected = $this->model->find($data->fin_category_id);
        
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data','principal','rota','primaryKey','categories','categoriesSelected'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }


}
