<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\User;
use App\Models\Painel\Contract;
use App\Models\Painel\ContractUser;
use Illuminate\Support\Facades\Auth;
use Defender;

class ContractController extends StandardController {

  protected $nameView = 'contract';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_contract';

  public function __construct(Request $request, Contract $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getUser() {
    if (Defender::hasPermission("contract/user")) {
      $data = ContractUser::join('users','users.id', 'contract_user.id_user')
                          ->selectRaw('contract_user.*, users.name')
                          ->orderBy('id_contract_user','desc')
                          ->paginate($this->totalItensPorPagina);

      $principal = $this->diretorioPrincipal;
      $primaryKey = 'id_contract_user';
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.contract-user-index", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getUserNew() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $users = User::get();
      return view("{$this->diretorioPrincipal}.{$this->nameView}.contract-user-new", compact('principal','users','rota','primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }


  public function postStoreUserNew() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();

      $dadosForm['start_date'] = date('Y-m-d', strtotime($dadosForm['start_date']));
      $dadosForm['end_date'] = date('Y-m-d', strtotime($dadosForm['end_date']));

      $dadosForm['id_contract'] = $this->model->where('status', 1)->first()->id_contract;

      $ContractUser = new ContractUser;
      $validator = $this->validator->make($dadosForm, $ContractUser->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/user-new")->withErrors($validator)->withInput();
      }
      ContractUser::create($dadosForm);

      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/user");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getShowUserNew($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = ContractUser::findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $users = User::get();
      $userSelected = User::find($data->id_user);

      return view("{$this->diretorioPrincipal}.{$this->nameView}.contract-user-new", compact('data','users','userSelected','principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postUpdateUserNew($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();

      $dadosForm['start_date'] = date('Y-m-d', strtotime($dadosForm['start_date']));
      $dadosForm['end_date'] = date('Y-m-d', strtotime($dadosForm['end_date']));
      $dadosForm['id_contract'] = $this->model->where('status', 1)->first()->id_contract;

      $ContractUser = new ContractUser;
      $validator = $this->validator->make($dadosForm, $ContractUser->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show-user-new/$id")->withErrors($validator)->withInput();
      }
      $data = ContractUser::findOrFail($id)->update($dadosForm);

      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/user");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getMyContracts() {
    if (Defender::hasPermission("contract/my-contracts")) {
      $data = ContractUser::join('contract','contract.id_contract', 'contract_user.id_contract')
      ->selectRAW('contract.title, contract_user.*')
      ->where('id_user', Auth::user()->id)
      ->paginate($this->totalItensPorPagina);

      $principal = $this->diretorioPrincipal;
      $primaryKey = 'id_contract_user';
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.my-contracts", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getMyContract($idContract) {
    if (Defender::hasPermission("contract/my-contracts")) {
      $data = $this->model->join('contract_user','contract_user.id_contract', 'contract.id_contract')
      ->where('id_user', Auth::user()->id)
      ->where('contract_user.id_contract_user', $idContract)
      ->selectRaw('contract.*, contract_user.id_contract_user, contract_user.rev_share')
      ->first();

      $principal = $this->diretorioPrincipal;
      $primaryKey = 'id_contract_user';
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.my-contract", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postSignature() {
    
    // if (Defender::hasPermission("contract")) {
      
      $dadosForm = $this->request->all();
      if(isset($dadosForm['signature'])){
        $img = $dadosForm['signature'];
      }else{
        $img = $dadosForm['signature_admin'];
      }

      
      $img = str_replace('data:image/png;base64,', '', $img);
      $img = str_replace(' ', '+', $img);
      $data = base64_decode($img);
      $nameSignature = 'signature-'.$dadosForm['id_contract_user'].'-'.Auth::user()->id.'-'.uniqid().'.png';
      file_put_contents("assets/painel/uploads/signature/$nameSignature", $data);

      if(isset($dadosForm['signature'])){
        $save['signature'] = $nameSignature;
      }else{
        $save['signature_admin'] = $nameSignature;
      }

      $save['status'] = $dadosForm['status'];

      ContractUser::find($dadosForm['id_contract_user'])->update($save);

      if(isset($dadosForm['signature'])){
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/my-contracts");
      }else{
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/signature-admin");
      }

    // } else {
    //   return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function getSignatureAdmin(){
    if (Defender::hasPermission("contract")) {
      $data = $this->model->join('contract_user','contract_user.id_contract', 'contract.id_contract')
      ->join('users','users.id', 'contract_user.id_user')
      ->selectRaw('contract.*, contract_user.id_contract_user, users.name, contract_user.rev_share, contract_user.status, contract_user.signature, contract_user.signature_admin')
      ->paginate($this->totalItensPorPagina);

      $principal = $this->diretorioPrincipal;
      $primaryKey = 'id_contract_user';
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.all-contracts", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getSignatureAdminContract($idContract) {
    if (Defender::hasPermission("contract")) {
      $data = $this->model->join('contract_user','contract_user.id_contract', 'contract.id_contract')
      ->where('contract_user.id_contract_user', $idContract)
      ->selectRaw('contract.*, contract_user.id_contract_user, contract_user.rev_share, contract_user.pay_time')
      ->first();

      $principal = $this->diretorioPrincipal;
      $primaryKey = 'id_contract_user';
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.contract", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getSeeContract($idContract){
    $data = $this->model->join('contract_user','contract_user.id_contract', 'contract.id_contract')
    ->where('contract_user.id_contract_user', $idContract)
    ->selectRaw('contract.*, contract_user.id_contract_user, contract_user.rev_share, contract_user.pay_time, contract_user.signature, contract_user.signature_admin')
    ->first();

    return view("{$this->diretorioPrincipal}.{$this->nameView}.see-contract", compact('data'));
  }



}
