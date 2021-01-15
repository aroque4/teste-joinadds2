<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Domain;
use App\Models\Painel\DomainChecklist;
use App\Models\Painel\DomainConfirmChecklist;
use App\Models\Painel\DomainChecklistObservation;
use Illuminate\Support\Facades\Auth;
use Defender;

class DomainChecklistController extends StandardController {

  protected $nameView = 'domain-checklist';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_domain_checklist';

  public function __construct(Request $request, DomainChecklist $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getChecklist(){
    if (Defender::hasPermission("{$this->nameView}/checklist")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $domains = Domain::where('status_checklist',1)->get();
      $domainChecklist = DomainChecklist::where('status','1')->orderBy('ordem','asc')->get();
      $domainConfirmChecklist = DomainConfirmChecklist::get();
      $domainObservations = DomainChecklistObservation::leftJoin('users as U', 'U.id','=','domain_checklist_observation.id_user')
                                                     ->selectRaw('
                                                                domain_checklist_observation.description,
                                                                domain_checklist_observation.created_at,
                                                                domain_checklist_observation.id_domain,
                                                                U.name')
                                                      ->get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.check", compact('domains','domainChecklist','domainConfirmChecklist', 'principal','rota', 'primaryKey','domainObservations'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getChecked($idDomain, $idChecklist){
    if (Defender::hasPermission("{$this->nameView}/checklist")) {
      $dataForm['id_domain'] = $idDomain;
      $dataForm['id_domain_checklist'] = $idChecklist;
      DomainConfirmChecklist::create($dataForm);
      return 1;
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getUnchecked($idDomain, $idChecklist){
    if (Defender::hasPermission("{$this->nameView}/checklist")) {

      DomainConfirmChecklist::where('id_domain',$idDomain)
                            ->where('id_domain_checklist',$idChecklist)
                            ->delete();
      return 1;
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postSaveObservation(){

    if (Defender::hasPermission("{$this->nameView}/checklist")) {

      $dataForm['description'] = $_POST['value'];
      $dataForm['id_domain'] = $_POST['id_domain'];
      $dataForm['id_user'] = Auth::user()->id;
      DomainChecklistObservation::create($dataForm);

      return 1;
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getRemove($idDomain){
    Domain::find($idDomain)->update(['status_checklist' => 0]);
    return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/checklist");
  }
}
