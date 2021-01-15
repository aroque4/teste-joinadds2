<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\FinBank;
use App\Models\Painel\FinCurrency;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class FinBankController extends StandardController {

  protected $nameView = 'fin-bank';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_fin_bank';

  public function __construct(Request $request, FinBank $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getIndex() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model
                    ->join('fin_currency', 'fin_currency.id_fin_currency', 'fin_bank.id_fin_currency')
                    ->selectRaw('fin_bank.*, fin_currency.name nameCurrency')
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
      $currencies = FinCurrency::get();

      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey', 'currencies'));
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
  
      $currencies = FinCurrency::get();
      $currenciesSelected = FinCurrency::find($data->id_fin_currency);
  
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data','principal','rota','primaryKey','currencies','currenciesSelected'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }


}
